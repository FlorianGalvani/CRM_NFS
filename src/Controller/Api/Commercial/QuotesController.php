<?php

namespace App\Controller\Api\Commercial;

use App\Entity\User;
use App\Service\Emails\SendEmail;
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
    function getFormData(Request $request)
    {
        $response = [
            'success' => false
        ];

        $formData = [
            'commercial' => [
                'firstname' => $this->currentUser->getFirstname(),
                'lastname' => $this->currentUser->getLastname(),
            ],
            'company' => json_decode($this->currentUser->getAccount()->getCompanyData(),true),
        ];
        $customers = $this->currentUser->getAccount()->getCustomers();
        $formData['customers'] = $customers;
        $response['formData'] = $formData;
        $response['success'] = true;

        return $this->json($response,Response::HTTP_OK);
    }
    #[Route('/api/commercial/quotes/new', name: 'app_api_commercial_quotes_create')]
    function createNewQuotes (Request $request, ManagerRegistry $managerRegistry, UserPasswordHasherInterface $passwordHasher, SendEmail $sendEmail)
    {
        $response = [
            'success' => false
        ];

        if(!$request->isXmlHttpRequest()) {
            return $this->json($response,Response::HTTP_UNAUTHORIZED);
        }
        $formData = json_decode($request->getContent(), true);

        $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $currentUser = $managerRegistry->getRepository(User::class)->findOneBy(['email' => $decodedToken['username']]);
        $document = new \App\Entity\Document();
        $document->setType(\App\Enum\Document\DocumentType::QUOTE);
        $document->setFileName($formData['fileName']);
        $document->setFileExtension($formData['fileExtension']);
        $customer = $managerRegistry->getRepository(\App\Entity\Account::class)->find($formData['customer']);
        $document->setCustomer($customer);
        $commercial = $managerRegistry->getRepository(\App\Entity\Account::class)->find($currentUser->getAccount()->getId());
        $document->setCommercial($commercial);
        $document->setData(json_encode($formData['data']));
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
        $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $currentUser = $managerRegistry->getRepository(User::class)->findOneBy(['email' => $decodedToken['username']]);
        $quotes = $managerRegistry->getRepository(\App\Entity\Document::class)->findBy(['commercial' => $currentUser->getAccount()->getId(), 'type' => \App\Enum\Document\DocumentType::QUOTE]);
        $response['quotes'] = json_encode($quotes[0]->getData());

        $jsonContent = $serializer->serialize($quotes, 'json');


        dd(json_decode($jsonContent, true));
        $response['success'] = true;
        return $this->json($jsonContent,Response::HTTP_OK);
    }
}
