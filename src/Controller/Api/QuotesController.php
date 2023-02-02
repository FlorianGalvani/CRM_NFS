<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Account;
use App\Entity\User;
use App\Event\CreateDocumentEvent;
use App\Service\Emails\SendEmail;
use App\Service\FileUploader;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

class QuotesController extends BaseController
{
    #[Route('/api/commercial/quotes/formdata')]
    function getFormData(Request $request, ManagerRegistry $managerRegistry)
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
        foreach ($customers as $customer)  {
            $customerData = json_decode($customer->getData(),true);
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

        return $this->json($response,Response::HTTP_OK);
    }

    #[Route('/api/commercial/quotes/new', name: 'app_api_commercial_quotes_create')]
    function createNewQuotes (Request $request,FileUploader $fileUploader, ManagerRegistry $managerRegistry, UserPasswordHasherInterface $passwordHasher, SendEmail $sendEmail, EventDispatcherInterface $eventDispatcher)
    {
        $response = [
            'success' => false
        ];

        if(!$request->isXmlHttpRequest()) {
            return $this->json($response,Response::HTTP_UNAUTHORIZED);
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

        return $this->json($response,Response::HTTP_OK);
    }

    #[Route('/api/commercial/quotes/list', name: 'app_api_commercial_quotes_list')]
    function listQuotes (Request $request,SerializerInterface $serializer, ManagerRegistry $managerRegistry, UserPasswordHasherInterface $passwordHasher, SendEmail $sendEmail)
    {
        $response = [
            'success' => false
        ];

        if(!$request->isXmlHttpRequest()) {
            return $this->json($response,Response::HTTP_UNAUTHORIZED);
        }

        $currentUser = $this->getUser();

        $quotes = $managerRegistry->getRepository(\App\Entity\Document::class)->findBy(['commercial' => $currentUser->getAccount()->getId(), 'type' => \App\Enum\Document\DocumentType::QUOTE]);
        return $this->json($quotes,Response::HTTP_OK);

        $i = 0;
        foreach ($quotes as $quote) {
            // $newQuote = [
            //     'id' => $quote->getId(),
                
            // ];
            // unset($quote['data']);
     
            // return $this->json($quote,Response::HTTP_OK);
            $response['quotes'][] = json_decode($quote->get());
        }



        $response['success'] = true;
        return $this->json($response,Response::HTTP_OK);
    }
}
