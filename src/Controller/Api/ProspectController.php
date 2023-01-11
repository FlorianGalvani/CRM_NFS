<?php

namespace App\Controller\Api;

use App\Entity\Prospect;
use App\Event\CreateProspectEvent;
use Doctrine\Persistence\ManagerRegistry;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProspectController extends AbstractController
{
    #[Route('/api/prospects', methods: ['POST'])]
    public function create(Request $request, ManagerRegistry $doctrine, EventDispatcherInterface $eventDispatcher): Response
    {
        $response = [
            'success' => false
        ];

        $user = $this->getUser();

        if($user == null) {
            throw $this->createAccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);

        $prospect = (new Prospect())
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setPhone($data['phone'])
            ->setEmail($data['email']);

        $prospect->setCommercial($user->getAccount());

        $entityManager = $doctrine->getManager();
        $entityManager->persist($prospect);
        $entityManager->flush();

        $response = [
            'success' => true,
            'data' => $prospect
        ];

        $eventDispatcher->dispatch(new CreateProspectEvent($prospect), CreateProspectEvent::NAME);

        return $this->json($response, Response::HTTP_CREATED);
    }
}