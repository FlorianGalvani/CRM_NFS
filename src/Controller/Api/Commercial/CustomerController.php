<?php

namespace App\Controller\Api\Commercial;

use App\Form\Commercial\NewCustomerType;
use App\Entity\User;
use App\Service\Emails\SendEmail;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class CustomerController extends AbstractController
{

    private $jwtManager = null;
    private $tokenStorageInterface = null;

    public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
    }

    #[Route('/api/commercial/new/customer', name: 'app_api_commercial_crud')]
    public function createNewCustomer(Request $request, ManagerRegistry $managerRegistry, UserPasswordHasherInterface $passwordHasher, SendEmail $sendEmail): Response
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
        $existingUser = $managerRegistry->getRepository(User::class);
        $existingUser = $existingUser->findOneBy(['email' => $data['email']]);
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
        $currentUser = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
       
        $currentCommercial = $managerRegistry->getRepository(User::class)->findOneBy(['email' => $currentUser['username']]);
        $account->setCommercial($currentCommercial->getAccount());
        $em->persist($account);
        $user->setAccount($account);
        $password = "pass_1234";
        $passwordHash = $passwordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($passwordHash);
       
        $em->persist($user);
        $em->flush();

        $sendEmail->sendNewCustomerEmail($user, $password);

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
