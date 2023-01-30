<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\User;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransactionController extends BaseController
{
    private $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
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
    public function paymentSuccess($id): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentAccount = $user->getAccount();

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
            $invoice = $transaction->getTransactionInvoice();
            $transaction->setPaymentStatus(Transaction::TRANSACTION_STATUS_PAYMENT_SUCCESS);
            $transaction->setLabel('Règlement d\'une facture');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'transaction' => $transaction
            ]);
        } catch (Error $e) {
            http_response_code(500);
            return $this->json(['error' => $e->getMessage()]);
        }
    }
}