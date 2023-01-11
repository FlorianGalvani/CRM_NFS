<?php

namespace App\EventSubscriber;

use App\Entity\CustomerEvent;
use App\Event\CreateProspectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;

class CustomerSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onCreateProspect(CreateProspectEvent $event): void
    {
        $prospect = $event->getProspect();

        $customerEvents = (new CustomerEvent())
            ->setProspect($prospect)
            ->setEvents([
                'prospect_created' => new \DateTime()
            ]);

        $this->em->persist($customerEvents);
        $this->em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CreateProspectEvent::NAME => 'onCreateProspect',
        ];
    }

}