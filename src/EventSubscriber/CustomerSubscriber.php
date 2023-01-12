<?php

namespace App\EventSubscriber;

use App\Entity\CustomerEvent;
use App\Enum\Customer\EventType;
use App\Event\CreateCustomerEvent;
use App\Event\CreateProspectEvent;
use App\Repository\CustomerEventRepository;
use App\Repository\ProspectRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;

class CustomerSubscriber implements EventSubscriberInterface
{
    private $em;
    private $customerEventRepository;
    private $prospectRepository;

    public function __construct(
        EntityManagerInterface $em,
        CustomerEventRepository $customerEventRepository,
        ProspectRepository $prospectRepository
    )
    {
        $this->em = $em;
        $this->customerEventRepository = $customerEventRepository;
        $this->prospectRepository = $prospectRepository;
    }

    public function onCreateProspect(CreateProspectEvent $event): void
    {
        $prospect = $event->getProspect();

        $customerEvents = (new CustomerEvent())
            ->setProspect($prospect)
            ->setEvents([
                EventType::EVENT_PROSPECT_CREATED => new \DateTime()
            ]);

        $this->em->persist($customerEvents);
        $this->em->flush();
    }

    public function onCreateCustomer(CreateCustomerEvent $event)
    {
        $customer = $event->getCustomer();

        $existingProspect = $this->prospectRepository->findOneBy(['email' => $customer->getUser()->getEmail()]);
        $_event = [EventType::EVENT_CUSTOMER_CREATED => new \DateTime()];

        if($existingProspect !== null) {
            $customerEvent = $this->customerEventRepository->findOneBy(['prospect' => $existingProspect]);

            $events = $customerEvent->getEvents();
            $events[] = $_event;

            $customerEvent->setEvents($events);

            $this->em->persist($customerEvent);
        } else {
            $customerEvent = (new CustomerEvent())
                ->setEvents($_event);
        }
        $customerEvent->setCustomer($customer);

        $this->em->flush();

        //todo: envoyer email
            // récupérer le mot de passe décrypter ? // on peut pas sur Symfo => sécu
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CreateProspectEvent::NAME => 'onCreateProspect',
            CreateCustomerEvent::NAME => 'onCreateCustomer'
        ];
    }

}