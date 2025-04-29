<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleLoginController extends AbstractController
{
    private $httpClient;
    private $entityManager;
    private $tokenStorage;

    public function __construct(
        HttpClientInterface $httpClient,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/login/google', name: 'login_google')]
    public function redirectToGoogle(): Response
    {
        // Paramètres OAuth
        $clientId = $_ENV['GOOGLE_ID'];
        $redirectUri = 'http://localhost/EduTripWeb/EduTripWeb/public/login/google/callback';
        $scope = 'email profile';

        // Construction de l'URL de redirection
        $googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scope,
            'access_type' => 'online',
            'prompt' => 'select_account'
        ];

        $url = $googleAuthUrl . '?' . http_build_query($params);

        return $this->redirect($url);
    }

    #[Route('/login/google/callback', name: 'login_google_callback')]
    public function handleGoogleCallback(Request $request): Response
    {
        // Récupérer le code d'autorisation de Google
        $code = $request->query->get('code');

        if (!$code) {
            $this->addFlash('error', 'Aucun code d\'autorisation reçu de Google.');
            return $this->redirectToRoute('app_login');
        }

        try {
            // Paramètres pour l'échange du code
            $clientId = $_ENV['GOOGLE_ID'];
            $clientSecret = $_ENV['GOOGLE_SECRET'];
            $redirectUri = 'http://localhost/EduTripWeb/EduTripWeb/public/login/google/callback';

            // Échanger le code contre un token d'accès
            $tokenResponse = $this->httpClient->request('POST', 'https://oauth2.googleapis.com/token', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'body' => http_build_query([
                    'code' => $code,
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code'
                ])
            ]);

            if ($tokenResponse->getStatusCode() !== 200) {
                $this->addFlash('error', 'Erreur lors de l\'échange du code: ' . $tokenResponse->getContent(false));
                return $this->redirectToRoute('app_login');
            }

            $tokenData = $tokenResponse->toArray();

            if (!isset($tokenData['access_token'])) {
                $this->addFlash('error', 'Token d\'accès non reçu.');
                return $this->redirectToRoute('app_login');
            }

            // Récupérer les informations de l'utilisateur
            $userInfoResponse = $this->httpClient->request('GET', 'https://www.googleapis.com/oauth2/v3/userinfo', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $tokenData['access_token']
                ]
            ]);

            if ($userInfoResponse->getStatusCode() !== 200) {
                $this->addFlash('error', 'Erreur lors de la récupération des informations utilisateur.');
                return $this->redirectToRoute('app_login');
            }

            $userInfo = $userInfoResponse->toArray();

            // Vérifier si l'email est présent
            if (!isset($userInfo['email'])) {
                $this->addFlash('error', 'Email non reçu de Google.');
                return $this->redirectToRoute('app_login');
            }

            // Rechercher l'utilisateur dans la base de données
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['mail' => $userInfo['email']]);

            // Si l'utilisateur n'existe pas, le créer
            if (!$user) {
                $user = new User();
                $user->setMail($userInfo['email']);
                $user->setNom($userInfo['family_name'] ?? 'Utilisateur');
                $user->setPrenom($userInfo['given_name'] ?? 'Google');
                $user->setPassword(''); // Mot de passe vide car authentification OAuth
                $user->setRole('ROLE_USER');
                $user->setStatus('active');
                $user->setIsVerified(true); // L'utilisateur est vérifié car authentifié par OAuth
                $user->setTel('00000000'); // Valeur par défaut

                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }

            // Connecter l'utilisateur manuellement
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);
            $request->getSession()->set('_security_main', serialize($token));

            // Rediriger vers la page d'accueil ou le tableau de bord
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('admin_dashboard');
            }

            return $this->redirectToRoute('app_dashboard');

        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur s\'est produite: ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        }
    }
}
