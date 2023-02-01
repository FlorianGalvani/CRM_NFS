<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\User;
use App\Entity\Transaction;
use App\Repository\DocumentRepository;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransactionController extends BaseController
{
    private $transactionRepository;
    private $documentRepository;

    public function __construct(
        TransactionRepository $transactionRepository,
        DocumentRepository $documentRepository
    )
    {
        $this->transactionRepository = $transactionRepository;
        $this->documentRepository = $documentRepository;
    }

    #[Route('/api/stripe_create/{id}', methods: ['GET'])]
    public function stripeCreate($id): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentAccount = $user->getAccount();

        // Récupération de la transaction en cours s'il y en a une
        $transaction = $this->transactionRepository->findLastOneByAccountAndStatus(
            $id,
            $currentAccount,
            [Transaction::TRANSACTION_INVOICE_SENT, Transaction::TRANSACTION_STATUS_PAYMENT_INTENT]
        );

        if (null === $transaction) {
            throw $this->createNotFoundException();
        }

        \Stripe\Stripe::setApiKey($this->getParameter('app.stripe.keys.private'));
        try {
            $invoiceData = json_decode($transaction->getTransactionInvoice()->getData(), true);
            if (null === $transaction->getStripePaymentIntentId()) {
                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => $transaction->getAmount() * 100,
                    'currency' => 'eur',
                ]);
            } else {
                $paymentIntent = \Stripe\PaymentIntent::update(
                    $transaction->getStripePaymentIntentId(),
                    ['metadata' => [
                        'amount' => $transaction->getAmount() * 100,
                        'currency' => 'eur',
                    ]]
                );
            }

            $transaction->setPaymentStatus(Transaction::TRANSACTION_STATUS_PAYMENT_INTENT);
            $transaction->setStripePaymentIntentId($paymentIntent->id);
            $invoiceData['status'] = $transaction->getPaymentStatus();

            $transaction->getTransactionInvoice()->setData(json_encode($invoiceData));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
                'transaction' => $transaction,
                'factureDate' => $transaction->getTransactionInvoice()->getCreatedAt()
            ];

            return $this->json($output);
        } catch (Error $e) {
            http_response_code(500);

            return $this->json(['error' => $e->getMessage()]);
        }
    }

    #[Route('/api/payment_success/{id}', methods: ['GET'])]
    public function paymentSuccess($id, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentAccount = $user->getAccount();

        $payment_method_id = $request->get('pm');

        \Stripe\Stripe::setApiKey($this->getParameter('app.stripe.keys.private'));

        // Récupération de la transaction en cours s'il y en a une
        $transaction = $this->transactionRepository->findLastOneByAccountAndStatus(
            $id,
            $currentAccount,
            [Transaction::TRANSACTION_STATUS_PAYMENT_INTENT]
        );

        if (null === $transaction) {
            throw $this->createNotFoundException();
        }

        try {
            $invoiceData = $transaction->getTransactionInvoice()->getData();
            $transaction->setPaymentStatus(Transaction::TRANSACTION_STATUS_PAYMENT_SUCCESS);
            $transaction->setLabel('Règlement d\'une facture');

            $payment_method = \Stripe\PaymentMethod::retrieve($payment_method_id);

            $exp_month = '';
            $exp_month .= $payment_method->card->exp_month < 10 ? '0' : '';
            $exp_month .= $payment_method->card->exp_month;

            $userPaymentMethod = [
                'card' => [
                    'brand' => $payment_method->card->brand,
                    'country' => $payment_method->card->country,
                    "exp_month" => $exp_month,
                    "exp_year" => $payment_method->card->exp_year,
                    "last4" => $payment_method->card->last4
                ]
            ];

            $invoiceData['status'] = $transaction->getPaymentStatus();
            $user->getAccount()->setPaymentMethod(json_encode($userPaymentMethod));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Votre paiement de '.$transaction->getAmount().' € a bien été pris en compte'
            ]);
        } catch (Error $e) {
            http_response_code(500);
            return $this->json(['error' => $e->getMessage()]);
        }
    }

    #[Route('/api/customer-transactions', methods: ['GET'])]
    public function dashboard()
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentAccount = $user->getAccount();

        $transactions = $this->transactionRepository->findAllBilledTransactionByAccount($currentAccount);
        $lastInvoice = $this->documentRepository->findLastOneInvoiceByAccount($currentAccount);
        $lastThreeQuotes = $this->documentRepository->findLastQuotesByAccount($currentAccount);

        $quotesData = [];

        foreach($lastThreeQuotes as $_quotes) {
            array_push($quotesData, $_quotes->getInfos());
        }

        try {
            return $this->json([
                'transactions' => $transactions,
                'lastInvoice' => $lastInvoice->getInfos(),
                'lastThreeQuotes' => $quotesData
            ]);
        } catch(Error $e) {
            return $this->json(['error' => $e->getMessage()]);
        }
    }

}