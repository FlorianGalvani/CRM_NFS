<?php

namespace App\Controller;

use App\Entity\User;
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

        $data = json_decode($request->getContent(), true);
        //TODO: Validate the data + check if the user already exists

        //Check if the user already exists
        $existingUser = $doctrine->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        
        if ($existingUser) {
            $response['message'] = 'User already exists';
            return $this->json($response, Response::HTTP_CONFLICT);
        }

        $entityManager = $doctrine->getManager();
        
        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($data['password']);
        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            $response['errors'] = $errors;
            return $this->json($response, Response::HTTP_BAD_REQUEST);
        }

        $password = $passwordHasher->hashPassword(
            $user,
            $data['password']
        );
        $user->setPassword($password);

        $entityManager->persist($user);
        $entityManager->flush();

        $response['success'] = true;
        return $this->json($response, Response::HTTP_CREATED);
    }

}

