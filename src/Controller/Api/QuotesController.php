<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Account;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class QuotesController extends BaseController
{
<<<<<<< HEAD
=======
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

>>>>>>> feature/devis-list-page
    #[Route('/api/commercial/quotes/formdata')]
    function getFormData( ManagerRegistry $managerRegistry)
    {
        $response = [
            'success' => false
        ];

        $currentUser = $this->getUser();

        $formData = [
            'commercial' => [
                'firstname' => $currentUser->getFirstname(),
                'lastname' => $currentUser->getLastname(),
            ],
<<<<<<< HEAD
            'company' => json_decode($currentUser->getAccount()->getData(),true),
=======
            'company' => json_decode($this->currentUser->getAccount()->getData(), true),
>>>>>>> feature/devis-list-page
        ];

        $customers = $managerRegistry->getRepository(Account::class)->findBy(['commercial' => $currentUser->getAccount()]);
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
<<<<<<< HEAD
       
            $currentUser = $this->getUser();
            $customer = $managerRegistry->getRepository(\App\Entity\Account::class)->find($formData['customer']);
            $commercial = $currentUser->getAccount();
=======

        $customer = $managerRegistry->getRepository(\App\Entity\Account::class)->find($formData['customer']);
        $commercial = $managerRegistry->getRepository(\App\Entity\Account::class)->find($this->currentUser->getAccount()->getId());
>>>>>>> feature/devis-list-page

        $document = new \App\Entity\Document();
        $document->setType(\App\Enum\Document\DocumentType::QUOTE);
        $document->setFileName('Devis ');
        $document->setFileExtension('pdf');
        $document->setCustomer($customer);
        $document->setCommercial($commercial);

        $document->setData(json_encode($formData['invoice']));

        $em = $managerRegistry->getManager();
        $em->persist($document);
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
            $findBy = ['commercial' => $currentUserAccountID];
        } else {
            $findBy = ['customer' => $currentUserAccountID];
        }

        $quotes = $managerRegistry->getRepository(\App\Entity\Document::class)->findBy($findBy, [
            'createdAt' => 'DESC'
        ]);
   
        return $this->json($quotes, Response::HTTP_OK);
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
        
        return $this->json($quotes, Response::HTTP_OK);
    }
}
