<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\User;
use App\Repository\CustomerEventRepository;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class CustomerEventsController extends BaseController
{
    private $eventRepo;
    
    public function __construct(CustomerEventRepository $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    #[Route('/all-customer-events', methods: ['GET'])]
    public function getAllProspects()
    {
        $customerEvents = $this->eventRepo->findAll();

        $customerEventsData = [];
        foreach($customerEvents as $event) {
            array_push($customerEventsData, $event->getInfos());
        }

        try {
            return $this->json($customerEventsData);
        } catch(Error $e) {
            return $this->json(['error' => $e->getMessage()]);
        }
    }
    
    #[Route('/commercial-customer-events', methods: ['GET'])]
    public function commercialCustomerEvents()
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentAccount = $user->getAccount();

        $customerEvents = $this->eventRepo->findCustomerEventsByCommercial($currentAccount);

        $customerEventsData = [];
        foreach($customerEvents as $event) {
            array_push($customerEventsData, $event->getInfos());
        }

        try {
            return $this->json($customerEventsData);
        } catch(Error $e) {
            return $this->json(['error' => $e->getMessage()]);
        }
    }
}