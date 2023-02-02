<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Account;
use App\Entity\User;
use App\Event\CreateDocumentEvent;
use Doctrine\Persistence\ManagerRegistry;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuotesController extends BaseController
{
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
            'company' => json_decode($currentUser->getAccount()->getData(),true),
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
    function createNewQuotes (Request $request, ManagerRegistry $managerRegistry, EventDispatcherInterface $eventDispatcher)
    {
        $response = [
            'success' => false
        ];

        if (!$request->isXmlHttpRequest()) {
            return $this->json($response, Response::HTTP_UNAUTHORIZED);
        }

        $formData = json_decode($request->getContent(), true);
        unset($formData['invoice']['logo']);
       
            $currentUser = $this->getUser();
            $customer = $managerRegistry->getRepository(\App\Entity\Account::class)->find($formData['customer']);
            $commercial = $currentUser->getAccount();

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

        $eventDispatcher->dispatch(new CreateDocumentEvent($document), CreateDocumentEvent::NAME);

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

        $currentUser = $this->getUser();
        $findBy = [];

        if ($currentUser->getAccount()->getType() === \App\Enum\Account\AccountType::COMMERCIAL) {
            $findBy = ['commercial' => $currentUser->getAccount()];
        } else {
            $findBy = ['customer' => $currentUser->getAccount()];
        }

        $quotes = $managerRegistry->getRepository(\App\Entity\Document::class)->findBy($findBy, [
            'createdAt' => 'DESC'
        ]);

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
        $currentUser = $this->getUser();

        if ($currentUser->getAccount()->getType() === \App\Enum\Account\AccountType::COMMERCIAL) {
            $findBy = ['commercial' => $currentUser->getAccount()];
        } else {
            $findBy = ['customer' => $currentUser->getAccount()];
        }

        $quotes = $managerRegistry->getRepository(\App\Entity\Document::class)->findBy($findBy, [
            'createdAt' => 'DESC'
        ], 5);

        return $this->json($quotes, Response::HTTP_OK);
    }
}
