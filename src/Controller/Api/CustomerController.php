<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\Commercial\NewCustomerType;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class CustomerController extends BaseController
{
    #[Route('/api/commercial/new/customer', name: 'app_api_commercial_crud')]
    public function createNewCustomer(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $em = $this->getManagerRegistry()->getManager();

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
        $currentUser = $this->getJWTTokenManagerInterface()->decode($this->getTokenStorageInterface()->getToken());

        $currentCommercial = $this->getManagerRegistry()->getRepository(User::class)->findOneBy(['email' => $currentUser['username']]);
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

        $response['success'] = true;
        return $this->json($response,Response::HTTP_OK);
    }

    #[Route('/api/commercial/quotes/new', name: 'app_api_commercial_quotes_create')]
    function createNewQuotes (Request $request)
    {
        $currentUser = $this->getUser();

        if($currentUser == null) {
            throw $this->createAccessDeniedException();
        }
        
        $response = [
            'success' => false
        ];

        if(!$request->isXmlHttpRequest()) {
            return $this->json($response,Response::HTTP_UNAUTHORIZED);
        }
        $decodedToken = $this->getJWTTokenManagerInterface()->decode($this->getTokenStorageInterface()->getToken());
        $currentUser = $this->getManagerRegistry()->getRepository(User::class)->findOneBy(['email' => $decodedToken['username']]);
        $formData = json_decode($request->getContent(), true);
        $document = new \App\Entity\Document();
        $document->setType(\App\Enum\Document\DocumentType::QUOTE);
        $document->setFileName($formData['fileName']);
        $document->setFileExtension($formData['fileExtension']);
        $customer = $this->getManagerRegistry()->getRepository(\App\Entity\Account::class)->find($formData['customer']);
        $document->setCustomer($customer);
        $commercial = $this->getManagerRegistry()->getRepository(\App\Entity\Account::class)->find($currentUser->getAccount()->getId());
        $document->setCommercial($commercial);
        $document->setData(json_encode($formData['data']));
        $em = $this->getManagerRegistry()->getManager();
        $em->persist($document);
        $em->flush();
        $response['success'] = true;
        return $this->json($response,Response::HTTP_OK);
    }

    #[Route('/api/commercial/quotes/list', name: 'app_api_commercial_quotes_list')]
    function listQuotes (Request $request,SerializerInterface $serializer)
    {
        $currentUser = $this->getUser();

        if($currentUser == null) {
            throw $this->createAccessDeniedException();
        }
        
        $response = [
            'success' => false
        ];

        if(!$request->isXmlHttpRequest()) {
            return $this->json($response,Response::HTTP_UNAUTHORIZED);
        }
        $quotes = $this->getManagerRegistry()->getRepository(\App\Entity\Document::class)->findBy(['commercial' => $currentUser->getAccount()->getId(), 'type' => \App\Enum\Document\DocumentType::QUOTE]);
        $response['quotes'] = json_encode($quotes[0]->getData());
        
        $jsonContent = $serializer->serialize($quotes, 'json');
    
        $response['success'] = true;
        $response['data'] = $jsonContent;
        return $this->json($response,Response::HTTP_OK);
    }
    
}
