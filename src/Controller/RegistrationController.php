<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\ApiEmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(ApiEmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $token = $request->get('token');

        if (null === $token) {
            $this->addFlash('error', 'Le token de vérification est manquant.');
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->findOneBy(['confirmationToken' => $token]);

        if (null === $user) {
            $this->addFlash('error', 'Le token de vérification est invalide ou a expiré.');
            return $this->redirectToRoute('app_register');
        }

        // Valider le token de vérification d'email
        $success = $this->emailVerifier->handleEmailConfirmation($request, $user);

        if (!$success) {
            $this->addFlash('error', 'Le token de vérification est invalide ou a expiré.');
            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre adresse email a été vérifiée avec succès.');

        return $this->redirectToRoute('app_login');
    }

    #[Route('/verify/resend', name: 'app_verify_resend_email')]
    public function resendVerifyEmail(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour demander un nouvel email de vérification.');
            return $this->redirectToRoute('app_login');
        }

        if ($user->isVerified()) {
            $this->addFlash('success', 'Votre adresse email est déjà vérifiée.');
            return $this->redirectToRoute('app_dashboard');
        }

        // Vérifier la validité de l'email avec l'API
        $emailValidation = $this->emailVerifier->validateEmail($user->getMail());

        if (!$emailValidation['is_valid_format'] || $emailValidation['deliverability'] === 'UNDELIVERABLE') {
            $this->addFlash('error', 'Votre adresse email semble invalide. Veuillez mettre à jour votre profil avec une adresse email valide.');
            return $this->redirectToRoute('app_profile_edit');
        }

        if ($emailValidation['is_disposable_email']) {
            $this->addFlash('error', 'Les adresses email temporaires ne sont pas autorisées. Veuillez utiliser une adresse email permanente.');
            return $this->redirectToRoute('app_profile_edit');
        }

        // Envoyer un nouvel email de vérification
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('noreply@edutrip.com', 'EduTrip'))
                ->to($user->getMail())
                ->subject('Confirmation de votre adresse email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        $this->addFlash('success', 'Un nouvel email de vérification a été envoyé.');

        return $this->redirectToRoute('app_dashboard');
    }
}
