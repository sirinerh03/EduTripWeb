<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\ProfileType;
use App\Form\LoginFormType;
use App\Service\ApiEmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\HttpFoundation\RequestStack;



class SecurityController extends AbstractController

{

    private TokenStorageInterface $tokenStorage;
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils, \App\Service\RecaptchaService $recaptchaService): Response
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
            'error' => $error,
            'recaptcha_site_key' => $recaptchaService->getSiteKey()
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        \App\Service\EmailValidatorService $emailValidator,
        \App\Service\RecaptchaService $recaptchaService,
        ApiEmailVerifier $emailVerifier
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            // Vérifier que les mots de passe correspondent
            if ($form->get('password')->getData() !== $form->get('confirmPassword')->getData()) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
    
            // Validation du numéro de téléphone
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
    
            // Validation des noms (prénom, nom)
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
    
            // Vérification de l'email avec le service externe
            $email = $form->get('mail')->getData();
            [$isValidEmail, $emailErrorMessage] = $emailValidator->validateEmail($email);
            if (!$isValidEmail) {
                $this->addFlash('error', $emailErrorMessage);
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
    
            // Vérifier si l'email existe déjà dans la base de données
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['mail' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
    
            // Hash du mot de passe et enregistrement de l'utilisateur
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $user->setRole($form->get('role')->getData());
            $user->setStatus('active');
            $user->setIsVerified(false);
    
            try {
                // Enregistrement de l'utilisateur en base
                $entityManager->persist($user);
                $entityManager->flush();
    
                // Envoi du mail de confirmation
                $emailVerifier->sendEmailConfirmation(
                    'app_verify_email',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('noreply@edutrip.com', 'EduTrip'))
                        ->to($user->getMail())
                        ->subject('Confirmation de votre adresse email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );
    
                $this->addFlash('success', 'Votre compte a été créé avec succès. Veuillez vérifier votre email pour activer votre compte.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création de votre compte: ' . $e->getMessage());
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


    


    //cote login  controller:

#[Route('/google-api', name: 'google_api')]
public function googleApiSignUp(Request $request, EntityManagerInterface $entityManager): Response
{
    $clientId = '246355943534-amsvb04oiull1bmu1blferldutq705oh.apps.googleusercontent.com';
    $clientSecret = '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';
    $redirectUri = 'http://127.0.0.1:8000/google-api'; 

  
    $code = $request->query->get('code');
    if ($code) {
    
        $tokenResponse = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query([
                    'code' => $code,
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code',
                ]),
            ]
        ]));

        $data = json_decode($tokenResponse, true);
        if (isset($data['error'])) {
            return new Response('Error: ' . $data['error_description']);
        }

        $accessToken = $data['access_token'];

       
        $userInfoResponse = file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $accessToken);
        $userData = json_decode($userInfoResponse, true);

        $email = $userData['email'] ?? null;
        $name = $userData['name'] ?? null;

        if (!$email) {
            return new Response("
                <script>
                    alert('No email returned from Google.');
                    window.location.href = '" . $this->generateUrl('app_login') . "';
                </script>
            ");
        }

        // Check if user already exists
        $user = $entityManager->getRepository(User::class)->findOneBy(['mail' => $email]);
        
       
        

        if (!$user) {
           
            // Create new user
            $user = new User();
            $user->setMail($email);
            $user->setPassword('GOOGLE_ACCOUNT');
            $user->setRole('ROLE_USER');
            $user->setStatus('active');
            $user->setIsVerified(true);
            $user->setTel('21914613');
            $user->setNom("google");
            $user->setPrenom("google");
         

            

            $entityManager->persist($user);
            $entityManager->flush();

        }
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        // After sign up -> redirect to login page
        return new Response("
            <script>
                alert('Signup successful! Please login.');
                window.location.href = '" . $this->generateUrl('app_dashboard') . "';
            </script>
        ");
    }

    // Else: No code yet -> redirect user to Google's login/signup page
    $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'offline',
        'prompt' => 'consent',
    ]);

    return new RedirectResponse($url);
}


}