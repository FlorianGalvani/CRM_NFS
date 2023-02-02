<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Event\CreateCustomerEvent;
use App\Event\CreateDocumentEvent;
use App\Repository\AccountRepository;
use App\Repository\DocumentRepository;
use App\Repository\UserRepository;
use App\Service\Emails\SendEmail;
use App\Form\Commercial\NewCustomerType;
use App\Controller\BaseController;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CustomerController extends BaseController
{
    private $userRepo;
    private $accountRepo;
    private $documentRepository;

    public function __construct(UserRepository $userRepo, AccountRepository $accountRepo, DocumentRepository $documentRepository)
    {
        $this->userRepo = $userRepo;
        $this->accountRepo = $accountRepo;
        $this->documentRepository = $documentRepository;
    }

    #[Route('/api/commercial/new/customer', name: 'app_api_commercial_crud')]
    public function createNewCustomer(Request $request, ManagerRegistry $managerRegistry, UserPasswordHasherInterface $passwordHasher, SendEmail $sendEmail, EventDispatcherInterface $eventDispatcher): Response
    {

        $em = $managerRegistry->getManager();

        $response = [
            'success' => false
        ];

        if(!$request->isXmlHttpRequest()) {
            return $this->json($response,Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(NewCustomerType::class, [], ['csrf_protection' => false]);
        $existingUser = $this->getManagerRegistry()->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            $response['message'] = 'User already exists';
            return $this->json($response, Response::HTTP_CONFLICT);
        }

        $form->submit($data);

        if (!$form->isValid()) {
            $response['errors'] = $form->getErrors(true);
            return $this->json($response, Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_USER']);
        $user->setFirstName($data['firstname']);
        $user->setLastName($data['lastname']);
        $user->setPhone($data['phone']);
        $user->setAddress($data['address']);
        $user->setEmailVerificationToken(bin2hex(random_bytes(32)));
        $user->setEmailVerificationTokenAt(new \DateTimeImmutable());
        $account = new \App\Entity\Account();
        $account->setType(\App\Enum\Account\AccountType::CUSTOMER);
        $account->setName($data['firstname'] . ' ' . $data['lastname']);
        $account->setAccountStatus(\App\Entity\Account::ACCOUNT_STATUS_PENDING);
        $currentUser = $this->getUser();

        $currentCommercial = $this->getManagerRegistry()->getRepository(User::class)->findOneBy(['email' => $currentUser->getEmail()]);
        $account->setCommercial($currentCommercial->getAccount());
        $em->persist($account);
        $user->setAccount($account);
        $password = 'pass_1234';
        $passwordHash = $passwordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($passwordHash);
       
        $em->persist($user);
        $em->flush();

        $sendEmail->sendNewCustomerEmail($user, $password); // => envoie d'email dans les event subscriber !

        $eventDispatcher->dispatch(new CreateCustomerEvent($account), CreateCustomerEvent::NAME);

        $response['success'] = true;
        return $this->json($response,Response::HTTP_OK);
    }

    #[Route('/api/commercial-customers', methods: ['GET'])]
    public function commercialCustomers()
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentAccount = $user->getAccount();

        $customers = $this->accountRepo->findCustomersByCommercial($currentAccount);

        $customersData = [];
        foreach($customers as $customer) {
            array_push($customersData, $customer->getInfos());
        }

        try {
            return $this->json($customersData);
        } catch(Error $e) {
            return $this->json(['error' => $e->getMessage()]);
        }
    }

    #[Route('/api/customer-invoices', methods: ['GET'])]
    public function invoices(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentAccount = $user->getAccount();

        $invoices = $this->documentRepository->findAllInvoicesByAccount($currentAccount);

        $invoicesData = [];

        foreach($invoices as $_invoices) {
            array_push($invoicesData, $_invoices->getInfos());
        }

        try {
            return $this->json($invoicesData);
        } catch(Error $e) {
            http_response_code(500);

            return $this->json(['error' => $e->getMessage()]);
        }
    }

    #[Route('/api/customer-invoices/{id}', methods: ['GET'])]
    public function invoiceShow($id): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentAccount = $user->getAccount();

        $invoice = $this->documentRepository->findOneBy([
            'id' => $id,
            'customer' => $currentAccount
        ]);

        if($invoice === null) {
            throw $this->createNotFoundException();
        }

        try {
            return $this->json($invoice->getInfos());
        } catch(Error $e) {
            http_response_code(500);

            return $this->json(['error' => $e->getMessage()]);
        }
    }

    #[Route('/api/customer-quotes', methods: ['GET'])]
    public function quotes(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentAccount = $user->getAccount();

        $quotes = $this->documentRepository->findAllQuotesByAccount($currentAccount);

        $quotesData = [];

        foreach($quotes as $_quotes) {
            array_push($quotesData, $_quotes->getInfos());
        }

        try {
            return $this->json($quotesData);
        } catch(Error $e) {
            http_response_code(500);

            return $this->json(['error' => $e->getMessage()]);
        }
    }

    #[Route('/api/customer-quotes/{id}', methods: ['GET'])]
    public function quoteShow($id): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentAccount = $user->getAccount();

        $quote = $this->documentRepository->findOneBy([
            'id' => $id,
            'customer' => $currentAccount
        ]);

        if($quote === null) {
            throw $this->createNotFoundException();
        }

        try {
            return $this->json($quote->getInfos());
        } catch(Error $e) {
            http_response_code(500);

            return $this->json(['error' => $e->getMessage()]);
        }
    }
}
