<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class TestLoginController extends AbstractController
{
    #[Route('/test-login', name: 'app_test_login')]
    public function testLogin(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        AuthenticationUtils $authenticationUtils
    ): Response {
        // Si l'utilisateur est déjà connecté, rediriger en fonction du rôle
        if ($this->getUser()) {
            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                return $this->redirectToRoute('admin_dashboard');
            } else {
                return $this->redirectToRoute('app_dashboard');
            }
        }

        $error = null;
        $lastUsername = $authenticationUtils->getLastUsername();

        // Traitement du formulaire de connexion
        if ($request->isMethod('POST')) {
            $mail = $request->request->get('mail');
            $password = $request->request->get('password');

            // Rechercher l'utilisateur
            $user = $entityManager->getRepository(User::class)->findOneBy(['mail' => $mail]);

            if ($user && $passwordHasher->isPasswordValid($user, $password)) {
                // Connexion manuelle de l'utilisateur
                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $this->container->get('security.token_storage')->setToken($token);
                $request->getSession()->set('_security_main', serialize($token));

                // Rediriger en fonction du rôle
                if (in_array('ROLE_ADMIN', $user->getRoles())) {
                    return $this->redirectToRoute('admin_dashboard');
                } else {
                    return $this->redirectToRoute('app_dashboard');
                }
            } else {
                $error = 'Identifiants invalides';
            }
        }

        return $this->render('security/test_login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
