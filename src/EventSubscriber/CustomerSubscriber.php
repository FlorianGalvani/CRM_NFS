<?php

namespace App\EventSubscriber;

use App\Entity\CustomerEvent;
use App\Entity\Document;
use App\Entity\Transaction;
use App\Enum\Customer\EventType;
use App\Event\CreateCustomerEvent;
use App\Event\CreateDocumentEvent;
use App\Event\CreateProspectEvent;
use App\Repository\CustomerEventRepository;
use App\Repository\ProspectRepository;
use App\Service\Emails\SendEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;

class CustomerSubscriber implements EventSubscriberInterface
{
    private $em;
    private $customerEventRepository;
    private $prospectRepository;
    private $mailer;

    public function __construct(
        EntityManagerInterface $em,
        CustomerEventRepository $customerEventRepository,
        ProspectRepository $prospectRepository,
        SendEmail $mailer
    )
    {
        $this->em = $em;
        $this->customerEventRepository = $customerEventRepository;
        $this->prospectRepository = $prospectRepository;
        $this->mailer = $mailer;
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

    public function onCreateDocument(CreateDocumentEvent $event): void
    {
        $document = $event->getDocument();
        $documentData = json_decode($document->getData());

        $customerEvent = $this->customerEventRepository->findOneBy(['prospect' => $document->getCustomer()]);

        $transaction = (new Transaction())
            ->setCustomer($document->getCustomer());
        if($documentData['amount']) $transaction->setAmount($documentData['amount']);


        if($document->getType() === Document::TRANSACTION_DOCUMENT_QUOTATION) {
            $transaction->setPaymentStatus(Transaction::TRANSACTION_QUOTATION_SENT)
                ->setTransactionQuotation($document);
            $_event = [EventType::EVENT_QUOTATION_SENT => new \DateTime()];
        }

        if($document->getType() === Document::TRANSACTION_DOCUMENT_INVOICE) {
            $transaction->setPaymentStatus(Transaction::TRANSACTION_INVOICE_SENT)
                ->setTransactionInvoice($document);
            $_event = [EventType::INVOICE_SENT => new \DateTime()];
        }
        $events = $customerEvent->getEvents();
        $events[] = [EventType::EVENT_EMAIL_SENT => new \DateTime()];
        $events[] = $_event;

        $customerEvent->setEvents($events);

        $this->em->persist($transaction);
        $this->em->persist($customerEvent);
        $this->em->flush();
    }

    public function onCreateCustomer(CreateCustomerEvent $event)
    {
        $customer = $event->getCustomer();

        $events = [];
        $events[] = [EventType::EVENT_CUSTOMER_CREATED => new \DateTime()];
        $events[] = [EventType::EVENT_EMAIL_SENT => new \DateTime()];
        $customerEvent = (new CustomerEvent())
            ->setEvents($events);

        $customerEvent->setCustomer($customer);

        $this->em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CreateProspectEvent::NAME => 'onCreateProspect',
            CreateCustomerEvent::NAME => 'onCreateCustomer'
        ];
    }

}