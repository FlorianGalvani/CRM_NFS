<?php

namespace App\Controller\Api;

use App\Entity\Account;
use App\Entity\User;
use App\Enum\Account\AccountType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    #[Route('/api/users', methods: ['POST'])]
    public function signup(Request $request, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine, ValidatorInterface $validator): JsonResponse
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
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $user->setPassword($data['password']);
        $user->setPhone($data['phone']);
        $user->setAddress($data['address']);

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

        if($data['account']) {
            $account = new Account();

            switch($data['account']) {
                case AccountType::COMMERCIAL:
                    $account->setType(AccountType::COMMERCIAL);
                    break;
                case AccountType::ADMIN:
                    $account->setType(AccountType::ADMIN);
                    break;
            }

            $account->setName($user->getFirstname().' '.$user->getLastname());
            $account->setAccountStatus(Account::ACCOUNT_STATUS_ACTIVE);
            $entityManager->persist($account);

            $user->setAccount($account);
        }

        $entityManager->flush();

        $response = [
            'success' => true,
            'data' => $user
        ];
        return $this->json($response, Response::HTTP_CREATED);
    }
}

