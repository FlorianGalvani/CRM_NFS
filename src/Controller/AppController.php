<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    // regex to ignore api and email route
    /**
     * @Route("/{reactRouting}", name="index", requirements={"reactRouting"="^(?!api)(?!email).+"}, defaults={"reactRouting": null})
     */
    public function index(): Response
    {
        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }
}
