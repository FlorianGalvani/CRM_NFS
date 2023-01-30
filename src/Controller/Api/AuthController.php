<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Account;
use App\Entity\User;
use App\Enum\Account\AccountType;
use App\Service\Emails\SendEmail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends BaseController
{
    private $maiiler;

    public function __construct(SendEmail $mailer) {
        $this->mailer = $mailer;
    }

    #[Route('/api/users', methods: ['POST'])]
    public function signup(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $response = [
            'success' => false
        ];

        $data = json_decode($request->getContent(), true);

        $existingUser = $this->getManagerRegistry()->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        
        if ($existingUser) {
            $response['message'] = 'User already exists';
            return $this->json($response, Response::HTTP_CONFLICT);
        }

        $entityManager = $this->getManagerRegistry()->getManager();
        
        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_USER']);
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $user->setPhone($data['phone']);
        $user->setAddress($data['address']);
        $user->setEmailVerificationToken(bin2hex(random_bytes(32)));
        $user->setEmailVerificationTokenAt(new \DateTimeImmutable());

        $errors = $this->validateData($user);
        if($errors !== null) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
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
                case AccountType::CUSTOMER:
                    $account->setType(AccountType::CUSTOMER);
                    $currentUser = $this->getUser();
                    $account->setCommercial($currentUser->getAccount());
                    break;
                case AccountType::ADMIN:
                    $account->setType(AccountType::ADMIN);
                    break;
            }

            $account->setName($user->getFirstname().' '.$user->getLastname());
            $account->setAccountStatus(Account::ACCOUNT_STATUS_PENDING);
            $entityManager->persist($account);

            $user->setAccount($account);
        }

        $entityManager->flush();

        $this->mailer->sendNewCommercialEmail($user, $data['password']);

        $response = [
            'success' => true,
            'data' => $user
        ];
        return $this->json($response, Response::HTTP_CREATED);
    }
}

