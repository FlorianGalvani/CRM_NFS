<?php

namespace App\Service\Emails;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class SendEmail
{
    private $mailer;
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendNewCommercialEmail(User $user, string $password) {

        $templateEmail = (new TemplatedEmail())
            ->from('noreply@crmanager.comy')
            ->to($user->getEmail())
            ->subject('Votre compte commercial a été créé')
            ->htmlTemplate('emails/new_commercial.html.twig')
            ->context( [
                'username' => $user->getEmail(),
                'firstname' => $user->getFirstName(),
                'lastname' => $user->getLastName(),
                'password' => $password,
                'emailToken' => $user->getEmailVerificationToken(),
            ]);
        ;
        $this->mailer->send($templateEmail);
    }

    public function sendNewCustomerEmail(User $user, string $password) {

         $templateEmail = (new TemplatedEmail())
             ->from('noreply@crmanager.comy')
             ->to($user->getEmail())
             ->subject('Welcome to our website')
             ->htmlTemplate('emails/new_customer.html.twig')
             ->context( [
                 'username' => $user->getEmail(),
                 'firstname' => $user->getFirstName(),
                 'lastname' => $user->getLastName(),
                 'url' => 'http://localhost:8000/email/verify/'.$user->getEmailVerificationToken(),
             ]);
         ;
         $this->mailer->send($templateEmail);
    }

    public function sendTestEmail () {
        $templateEmail = (new TemplatedEmail())
        ->from('noreply@crmanager.comy')
        ->to('noreply@crmanager.comy')
        ->subject('Welcome to our website')
        ->htmlTemplate('emails/new_customer.html.twig')
        ->context( [
            'username' => 'john.doe@gmail.com',
            'password' => 'ryeyr',
            'firstname' => 'John',
            'lastname' => 'Doe'
        ]);
        $this->mailer->send($templateEmail);
    }
}