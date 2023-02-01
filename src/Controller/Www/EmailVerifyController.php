<?php

namespace App\Controller\Www;

use App\Entity\User;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EmailVerifyController extends BaseController
{
    #[Route('/email/verify/{token}', name: 'app_email_verify')]
    public function index($token,Request $request): Response
    {
        $user = $this->getManagerRegistry()->getRepository(User::class)->findOneBy(['emailVerificationToken' => $token]);

        if (!$user) {
            return $this->redirect('/dashboard');
        }

        $form = $this->createForm(\App\Form\EmailVerifyType::class,$user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setEmailVerified(true);
            $user->setEmailVerificationToken(null);
            $em = $this->getManagerRegistry()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirect('/dashboard');
        }

        return $this->render('email_verify/index.html.twig', [
            'controller_name' => 'EmailVerifyController',
            'form' => $form->createView(),
        ]);
    }
}
