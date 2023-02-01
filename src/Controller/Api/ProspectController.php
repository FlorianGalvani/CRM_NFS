<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Prospect;
use App\Event\CreateProspectEvent;
use App\Repository\ProspectRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProspectController extends BaseController
{
    private $prospectRepo;

    public function __construct(ProspectRepository $prospectRepo) {
        $this->prospectRepo = $prospectRepo;
    }
    #[Route('/api/all-prospects', methods: ['GET'])]
    public function getAllProspects()
    {
        $prospects = $this->prospectRepo->findAll();

        $prospectsData = [];
        foreach($prospects as $prospect) {
            array_push($prospectsData, $prospect->getInfos());
        }

        try {
            return $this->json($prospectsData);
        } catch(Error $e) {
            return $this->json(['error' => $e->getMessage()]);
        }
    }

    #[Route('/api/prospects', methods: ['POST'])]
    public function create(Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        $response = [
            'success' => false
        ];

        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        $prospect = (new Prospect())
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setPhone($data['phone'])
            ->setEmail($data['email']);

        $prospect->setCommercial($user->getAccount());

        $errors = $this->validateData($prospect);
        if($errors !== null) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager = $this->getManagerRegistry()->getManager();
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