<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class ApiAuthController extends AbstractController
{
    #[Route('/api/signup', name: 'app_api_signup', methods: ['POST'])]
    public function signup(Request $request, UserPasswordHasherInterface $passwordHasher,ManagerRegistry $doctrine, ValidatorInterface $validator): JsonResponse
    {
        $response = [
            'success' => false
        ];

        if(!$request->isXmlHttpRequest()) {
            return $this->json($response,Response::HTTP_UNAUTHORIZED);
        }

        $user = new User();
        $form = $this->createForm(UserFormType::class, $user, ['csrf_protection' => false]);
        $data = json_decode($request->getContent(), true);

        $existingUser = $doctrine->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        
        if ($existingUser) {
            $response['message'] = 'User already exists';
            return $this->json($response, Response::HTTP_CONFLICT);
        }

        $form->submit($data);

        if (!$form->isValid()) {
            $response['errors'] = $form->getErrors(true);
            return $this->json($response, Response::HTTP_BAD_REQUEST);
        }

        $password = $passwordHasher->hashPassword(
            $user,
            $data['password']
        );
        $user->setPassword($password);

        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();

        $response['success'] = true;
        return $this->json($response, Response::HTTP_CREATED);
    }

}

