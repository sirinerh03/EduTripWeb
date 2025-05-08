<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Service\RecaptchaService;

class TestController extends AbstractController
{
    #[Route('/test/login', name: 'app_test_login')]
    public function testLogin(AuthenticationUtils $authenticationUtils, RecaptchaService $recaptchaService): Response
    {
        // Si l'utilisateur est déjà connecté, rediriger en fonction du rôle
        if ($this->getUser()) {
            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                return $this->redirectToRoute('admin_dashboard');
            } else {
                return $this->redirectToRoute('app_dashboard');
            }
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'recaptcha_site_key' => $recaptchaService->getSiteKey()
        ]);
    }
}
