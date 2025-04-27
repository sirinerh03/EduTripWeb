<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\ProfileType;
use App\Form\LoginFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // If user is already logged in, redirect based on role
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
            'error' => $error
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ValidatorInterface $validator, \App\Service\EmailValidatorService $emailValidator): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Vérifier que les mots de passe correspondent
            if ($form->get('password')->getData() !== $form->get('confirmPassword')->getData()) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // Vérifier que le numéro de téléphone contient exactement 8 chiffres
            $tel = $form->get('tel')->getData();
            $telConstraint = new Regex([
                'pattern' => '/^[0-9]{8}$/',
                'message' => 'Le numéro de téléphone doit contenir exactement 8 chiffres.',
            ]);

            $telErrors = $validator->validate($tel, $telConstraint);
            if (count($telErrors) > 0) {
                $this->addFlash('error', $telErrors[0]->getMessage());
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // Vérifier que le prénom et le nom ne contiennent que des lettres
            $prenom = $form->get('prenom')->getData();
            $nom = $form->get('nom')->getData();
            $nameConstraint = new Regex([
                'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                'message' => 'Le prénom et le nom ne doivent contenir que des lettres.',
            ]);

            $prenomErrors = $validator->validate($prenom, $nameConstraint);
            $nomErrors = $validator->validate($nom, $nameConstraint);

            if (count($prenomErrors) > 0) {
                $this->addFlash('error', 'Le prénom ne doit contenir que des lettres.');
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            if (count($nomErrors) > 0) {
                $this->addFlash('error', 'Le nom ne doit contenir que des lettres.');
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // Vérifier si l'email est valide avec notre service de validation avancé
            $email = $form->get('mail')->getData();
            [$isValidEmail, $emailErrorMessage] = $emailValidator->validateEmail($email);

            if (!$isValidEmail) {
                $this->addFlash('error', $emailErrorMessage);
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            if ($form->isValid()) {
                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );

                // Définir le rôle et le statut
                $user->setRole($form->get('role')->getData());
                $user->setStatus('active');
                $user->setIsVerified(true); // L'email est déjà vérifié par notre service

                // Vérifier si l'email est déjà utilisé
                $existingUser = $entityManager->getRepository(User::class)->findOneBy(['mail' => $user->getMail()]);
                if ($existingUser) {
                    $this->addFlash('error', 'Cet email est déjà utilisé par un autre compte.');
                    return $this->render('security/register.html.twig', [
                        'registrationForm' => $form->createView(),
                    ]);
                }

                try {
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Votre compte a été créé avec succès ! Votre adresse email a été validée.');
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la création de votre compte : ' . $e->getMessage());
                }
            }
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode peut rester vide
        // Le système de sécurité de Symfony gère la déconnexion
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        return $this->render('security/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function editProfile(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si un nouveau mot de passe a été fourni
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $plainPassword
                    )
                );
            }

            try {
                $entityManager->flush();
                $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
                return $this->redirectToRoute('app_profile');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de votre profil : ' . $e->getMessage());
            }
        }

        return $this->render('security/edit_profile.html.twig', [
            'profileForm' => $form->createView(),
            'user' => $user
        ]);
    }
}