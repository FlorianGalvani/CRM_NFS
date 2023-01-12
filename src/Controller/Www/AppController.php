<?php

namespace App\Controller\Www;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends BaseController
{
    // regex to ignore api and email route
    /**
     * @Route("/{reactRouting}", name="index", requirements={"reactRouting"="^(?!api)(?!email).+"}, defaults={"reactRouting": null})
     */
    public function index(): Response
    {
        return $this->render('app/index.html.twig');
    }
}
