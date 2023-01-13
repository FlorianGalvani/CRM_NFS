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

    #[Route('/api/payment', methods: ['POST'])]
    public function payment(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentAccount = $user->getAccount();

        // Récupération de la provision en cours
        $this->transactionRepository = $this->getDoctrine()->getRepository(Transaction::class);

        $transaction = $this->transactionRepository->findLastOneByAccountAndStatus($currentAccount, [Transaction::TRANSACTION_QUOTATION_SENT]);

        if (null === $transaction) {
            throw $this->createNotFoundException();
        }

        return $this->json([
            'provision' => $transaction,
            'key' => $this->getParameter('app.stripe.keys.public'),
        ]);
    }

    #[Route('/api/stripe_create', methods: ['POST'])]
    public function stripeCreate(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentAccount = $user->getAccount();

        // Récupération de la transaction en cours s'il y en a une
        $transaction = $this->transactionRepository->findLastOneByAccountAndStatus($currentAccount, [Transaction::TRANSACTION_STATUS_PAYMENT_INTENT]);

        if (null === $transaction) {
            throw $this->createNotFoundException();
        }

        \Stripe\Stripe::setApiKey($this->getParameter('app.stripe.keys.private'));
        try {
            // Création de Stripe Payment Intent
            if (null === $transaction->getStripePaymentIntentId()) {
                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => $transaction->getAmount() * 100,
                    'currency' => 'eur',
                ]);
            } else {
                // ou mise à jour de Stripe Payment Intent
                $paymentIntent = \Stripe\PaymentIntent::update(
                    $transaction->getStripePaymentIntentId(),
                    ['metadata' => [
                        'amount' => $transaction->getAmount() * 100,
                        'currency' => 'eur',
                    ]]
                );
            }

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
            ];

            // Suivi de la transaction en cours
            $transaction->setPaymentStatus(Transaction::TRANSACTION_STATUS_PAYMENT_INTENT);
            $transaction->setStripePaymentIntentId($paymentIntent->id);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->json($output);
        } catch (Error $e) {
            http_response_code(500);

            return $this->json(['error' => $e->getMessage()]);
        }
    }
}