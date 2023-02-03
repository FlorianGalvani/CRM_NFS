<?php

namespace App\EventListener;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();
        $user = $this->userRepository->findOneBy(['email' => $payload['username']]);

        $payload['account'] = $user->getAccount()->getType();
        $payload['user'] = $user->getInfos();

        $event->setData($payload);
    }
}