<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Account;
use App\Entity\CustomerEvent;
use App\Entity\Transaction;
use App\Entity\User;
use App\Enum\Customer\EventType;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class QuotesController extends BaseController
{
    private $currentUser = null;

    private $jwtManager = null;
    private $tokenStorageInterface = null;

    function __construct(ManagerRegistry $managerRegistry, TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $this->currentUser = $managerRegistry->getRepository(User::class)->findOneBy(['email' => $decodedToken['username']]);
    }

    #[Route('/api/commercial/quotes/formdata')]
    function getFormData( ManagerRegistry $managerRegistry)
    {
        $response = [
            'success' => false
        ];

        $formData = [
            'commercial' => [
                'firstname' => $this->currentUser->getFirstname(),
                'lastname' => $this->currentUser->getLastname(),
            ],
            'company' => json_decode($this->currentUser->getAccount()->getData(), true),
        ];

        $customers = $managerRegistry->getRepository(Account::class)->findBy(['commercial' => $this->currentUser->getAccount()]);
        $customersData = [];
        $customersLabels = [];
        foreach ($customers as $customer) {
            $customerData = json_decode($customer->getData(), true);
            $customersData[] = [
                'data' => array(
                    'id' => $customer->getId(),
                    'name' => $customer->getName(),
                    'address' => $customerData['address'],
                    'zip' => $customerData['zipCode'],
                    'city' => $customerData['city'],
                    'country' => $customerData['country'],
                )
            ];
            $customersLabels[] = $customer->getName();
        }
        $formData['customers'] = $customersData;
        $formData['customersLabels'] = $customersLabels;
        $response['formData'] = $formData;
        $response['success'] = true;

        return $this->json($response, Response::HTTP_OK);
    }

    #[Route('/api/commercial/quotes/new', name: 'app_api_commercial_quotes_create')]
    function createNewQuotes(Request $request, ManagerRegistry $managerRegistry)
    {
        $response = [
            'success' => false
        ];

        if (!$request->isXmlHttpRequest()) {
            return $this->json($response, Response::HTTP_UNAUTHORIZED);
        }

        $formData = json_decode($request->getContent(), true);
        unset($formData['invoice']['logo']);

        $customer = $managerRegistry->getRepository(\App\Entity\Account::class)->find($formData['customer']);
        $commercial = $managerRegistry->getRepository(\App\Entity\Account::class)->find($this->currentUser->getAccount()->getId());

        $document = new \App\Entity\Document();
        $document->setType(\App\Enum\Document\DocumentType::QUOTE);
        $document->setFileName('Devis ');
        $document->setFileExtension('pdf');
        $document->setCustomer($customer);
        $document->setCommercial($commercial);

        $document->setData(json_encode($formData['invoice']));

        $em = $managerRegistry->getManager();
        $em->persist($document);
        $customerEvent = $em->getRepository(CustomerEvent::class)->findOneBy(['customer' => $document->getCustomer()]);
        $transaction = (new Transaction())
            ->setCustomer($document->getCustomer())
            ->setPaymentStatus(Transaction::TRANSACTION_QUOTATION_SENT)
            ->setTransactionQuotation($document)
            ->setLabel('Envoie d\'un devis ')
            ->setType('')
            ->setAmount($formData['subTotal']);
        $_event = [EventType::EVENT_QUOTATION_SENT => new \DateTime()];

        $events = $customerEvent->getEvents();
        $events[] = $_event;
        $customerEvent->setEvents($events);

        $em->persist($transaction);
        $document->setTransaction($transaction);
        $em->persist($customerEvent);
        $em->flush();

        $response['success'] = true;

        return $this->json($response, Response::HTTP_OK);
    }

    #[Route('/api/quotes/list', name: 'app_api_quotes_list')]
    function listQuotes(Request $request, ManagerRegistry $managerRegistry)
    {
        $response = [
            'success' => false
        ];

        if (!$request->isXmlHttpRequest()) {
            return $this->json($response, Response::HTTP_UNAUTHORIZED);
        }

        $findBy = [];
        $currentUserAccountID = $this->currentUser->getAccount()->getId();

        if ($this->currentUser->getAccount()->getType() === \App\Enum\Account\AccountType::COMMERCIAL) {
            $findBy = ['commercial' => $currentUserAccountID,
                'type' => \App\Enum\Document\DocumentType::QUOTE];
        } else if ($this->currentUser->getAccount()->getType() === \App\Enum\Account\AccountType::CUSTOMER){
            $findBy = ['customer' => $currentUserAccountID,
                'type' => \App\Enum\Document\DocumentType::QUOTE];
        } else if ($this->currentUser->getAccount()->getType() === \App\Enum\Account\AccountType::ADMIN) {
            $findBy = ['type' => \App\Enum\Document\DocumentType::QUOTE];
        }
        else {
            return $this->json($response, Response::HTTP_UNAUTHORIZED);
        }

        $quotes = $managerRegistry->getRepository(\App\Entity\Document::class)->findBy($findBy, [
            'createdAt' => 'DESC'
        ]);

        $quotesData = [];

        foreach($quotes as $_quote) {
            array_push($quotesData, $_quote->getInfos());
        }
   
        return $this->json($quotesData, Response::HTTP_OK);
    }

    #[Route('/api/quotes/list/latest', name: 'app_api_quotes_list_latest')]
    function listLatestQuotes(Request $request, ManagerRegistry $managerRegistry)
    {
        $response = [
            'success' => false
        ];

        if (!$request->isXmlHttpRequest()) {
            return $this->json($response, Response::HTTP_UNAUTHORIZED);
        }

        $findBy = [];
        $currentUserAccountID = $this->currentUser->getAccount()->getId();

        if ($this->currentUser->getAccount()->getType() === \App\Enum\Account\AccountType::COMMERCIAL) {
            $findBy = ['commercial' => $currentUserAccountID];
        } else {
            $findBy = ['customer' => $currentUserAccountID];
        }

        $quotes = $managerRegistry->getRepository(\App\Entity\Document::class)->findBy($findBy, [
            'createdAt' => 'DESC'
        ], 5);

        $quotesData = [];

        foreach($quotes as $_quote) {
            array_push($quotesData, $_quote->getInfos());
        }
        
        return $this->json($quotesData, Response::HTTP_OK);
    }
}
