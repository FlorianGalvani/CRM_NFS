<?php

namespace App\Controller\Api;

use App\Entity\Account;
use App\Entity\User;
use App\Service\Emails\SendEmail;
use App\Service\FileUploader;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

class QuotesController extends AbstractController
{
    private $currentUser = null;

    private $jwtManager = null;
    private $tokenStorageInterface = null;

    function __construct(ManagerRegistry $managerRegistry,TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $this->currentUser = $managerRegistry->getRepository(User::class)->findOneBy(['email' => $decodedToken['username']]);
    }

    #[Route('/api/commercial/quotes/formdata')]
    function getFormData(Request $request, ManagerRegistry $managerRegistry)
    {
        $response = [
            'success' => false
        ];

        $formData = [
            'commercial' => [
                'firstname' => $this->currentUser->getFirstname(),
                'lastname' => $this->currentUser->getLastname(),
            ],
            'company' => json_decode($this->currentUser->getAccount()->getData(),true),
        ];

        $customers = $managerRegistry->getRepository(Account::class)->findBy(['commercial' => $this->currentUser->getAccount()]);
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
    function createNewQuotes (Request $request,FileUploader $fileUploader, ManagerRegistry $managerRegistry, UserPasswordHasherInterface $passwordHasher, SendEmail $sendEmail)
    {
        $response = [
            'success' => false
        ];

        if(!$request->isXmlHttpRequest()) {
            return $this->json($response,Response::HTTP_UNAUTHORIZED);
        }

        $formData = json_decode($request->getContent(), true);
        unset($formData['invoice']['logo']);
       

            $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
            $currentUser = $managerRegistry->getRepository(User::class)->findOneBy(['email' => $decodedToken['username']]);
            $customer = $managerRegistry->getRepository(\App\Entity\Account::class)->find($formData['customer']);
            $commercial = $managerRegistry->getRepository(\App\Entity\Account::class)->find($currentUser->getAccount()->getId());

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

        $quotes = $managerRegistry->getRepository(\App\Entity\Document::class)->findBy(['commercial' => $this->currentUser->getAccount()->getId(), 'type' => \App\Enum\Document\DocumentType::QUOTE]);
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
