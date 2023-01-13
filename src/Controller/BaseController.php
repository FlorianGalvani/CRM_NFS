<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BaseController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            ValidatorInterface::class,
            ManagerRegistry::class,
            TokenStorageInterface::class,
            JWTTokenManagerInterface::class,
        ]);
    }

    protected function getUser(): User
    {
        $decodedToken = $this->getJWTTokenManagerInterface()->decode($this->getTokenStorageInterface()->getToken());
        return $this->getManagerRegistry()->getRepository(User::class)->findOneBy(['email' => $decodedToken['username']]);
    }

    protected function getTokenStorageInterface(): TokenStorageInterface
    {
        if (!$this->container->has(TokenStorageInterface::class)) {
            throw new \LogicException('The TokenStorageInterface is not registered in your application.');
        }

        return $this->container->get(TokenStorageInterface::class);
    }

    protected function getJWTTokenManagerInterface(): JWTTokenManagerInterface
    {
        if (!$this->container->has(JWTTokenManagerInterface::class)) {
            throw new \LogicException('The JWTTokenManagerInterface is not registered in your application.');
        }

        return $this->container->get(JWTTokenManagerInterface::class);
    }

    protected function getValidatorInterface(): ValidatorInterface
    {
        if (!$this->container->has(ValidatorInterface::class)) {
            throw new \LogicException('The ValidatorInterface is not registered in your application.');
        }

        return $this->container->get(ValidatorInterface::class);
    }

    protected function getManagerRegistry(): ManagerRegistry
    {
        if (!$this->container->has(ManagerRegistry::class)) {
            throw new \LogicException('The ManagerRegistry is not registered in your application.');
        }

        return $this->container->get(ManagerRegistry::class);
    }

    protected function getFormErrors(FormInterface $form): array
    {
        $errors = array();

        foreach ($form->getErrors() as $error) {
            $errors[$form->getName()][] = $error->getMessage();
        }

        foreach ($form as $child /** @var Form $child */) {
            if (!$child->isValid()) {
                foreach ($child->getErrors() as $error) {
                    $errors[$child->getName()][] = $error->getMessage();
                }
            }
        }

        return $errors;
    }

    protected function validateData($data): ?ConstraintViolationListInterface
    {
        $errors = $this->getValidatorInterface()->validate($data);

        if (count($errors) > 0) {
            return $errors;
        }
        return null;
    }
}