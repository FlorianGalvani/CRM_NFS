<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EmailVerifyController extends AbstractController
{
    #[Route('/email/verify/{token}', name: 'app_email_verify')]
    public function index($token,Request $request,ManagerRegistry $doctrine): Response
    {
        $user = $doctrine->getRepository(User::class)->findOneBy(['emailVerificationToken' => $token]);

        if (!$user) {
            return $this->redirect('/dashboard');
        }

        $form = $this->createForm(\App\Form\EmailVerifyType::class,$user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setEmailVerified(true);
            $user->setEmailVerificationToken(null);
            $em = $doctrine->getManager();
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
