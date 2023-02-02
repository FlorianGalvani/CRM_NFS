<?php

namespace App\Security;

use App\Entity\Account;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getAccountStatus() === Account::ACCOUNT_STATUS_DELETED) {
            // the message passed to this exception is meant to be displayed to the user
            throw new CustomUserMessageAccountStatusException('Votre compte n\'existe plus.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }
        if ($user->getAccountStatus() === Account::ACCOUNT_STATUS_PENDING) {
            throw new CustomUserMessageAccountStatusException('Vous n\'avez pas encore confirmé votre adresse email');
        }
        // user account is expired, the user may be notified
        if ($user->getAccountStatus() === Account::ACCOUNT_STATUS_DISABLED) {
            throw new CustomUserMessageAccountStatusException('Votre compte a été désactivé. Contactez le support pour plus d\'information');
        }
    }
}