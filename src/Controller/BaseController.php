<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            ValidatorInterface::class,
            ManagerRegistry::class,
        ]);
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