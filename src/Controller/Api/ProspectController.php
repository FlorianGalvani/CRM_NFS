<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProspectController
{
    #[Route('/api/users', name: 'app_api_signup', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $response = [
            'success' => false
        ];

        $data = json_decode($request->getContent(), true);

        return $this->json($response, Response::HTTP_CREATED);
    }
}