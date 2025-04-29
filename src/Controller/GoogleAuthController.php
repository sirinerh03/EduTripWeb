<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleAuthController extends AbstractController
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

    #[Route('/auth/google', name: 'app_auth_google')]
    public function redirectToGoogle(): Response
    {
        // Paramètres OAuth
        $clientId = $_ENV['GOOGLE_ID'];
        $redirectUri = 'http://localhost/EduTripWeb/EduTripWeb/public/connect/google/check';
        $scope = 'email profile';

        // Construction de l'URL de redirection
        $googleAuthUrl = 'https://accounts.google.com/o/oauth2/auth';
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        $url = $googleAuthUrl . '?' . http_build_query($params);

        return new RedirectResponse($url);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(Request $request): Response
    {
        // Récupérer le code d'autorisation de Google
        $code = $request->query->get('code');

        if (!$code) {
            $this->addFlash('error', 'Aucun code d\'autorisation reçu de Google.');
            return $this->redirectToRoute('app_login');
        }

        try {
            // Préparer les paramètres pour la requête
            $clientId = $_ENV['GOOGLE_ID'];
            $clientSecret = $_ENV['GOOGLE_SECRET'];
            $redirectUri = 'http://localhost/EduTripWeb/EduTripWeb/public/connect/google/check';

            // Afficher les informations de débogage
            $this->addFlash('info', 'Client ID: ' . $clientId);
            $this->addFlash('info', 'Redirect URI: ' . $redirectUri);

            // Échanger le code contre un token d'accès
            try {
                $response = $this->httpClient->request('POST', 'https://oauth2.googleapis.com/token', [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ],
                    'body' => [
                        'code' => $code,
                        'client_id' => $clientId,
                        'client_secret' => $clientSecret,
                        'redirect_uri' => $redirectUri,
                        'grant_type' => 'authorization_code'
                    ]
                ]);

                $statusCode = $response->getStatusCode();
                $this->addFlash('info', 'Status code: ' . $statusCode);

                if ($statusCode !== 200) {
                    $this->addFlash('error', 'Erreur HTTP: ' . $statusCode);
                    return $this->redirectToRoute('app_login');
                }

                $data = $response->toArray();
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'échange du code: ' . $e->getMessage());
                return $this->redirectToRoute('app_login');
            }

            if (!isset($data['access_token'])) {
                $this->addFlash('error', 'Impossible d\'obtenir le token d\'accès.');
                return $this->redirectToRoute('app_login');
            }

            // Récupérer les informations de l'utilisateur
            $userInfo = $this->httpClient->request('GET', 'https://www.googleapis.com/oauth2/v3/userinfo', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $data['access_token']
                ]
            ])->toArray();

            // Vérifier si l'utilisateur existe déjà
            $email = $userInfo['email'] ?? null;

            if (!$email) {
                $this->addFlash('error', 'Impossible de récupérer l\'email de l\'utilisateur.');
                return $this->redirectToRoute('app_login');
            }

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['mail' => $email]);

            // Si l'utilisateur n'existe pas, créer un nouvel utilisateur
            if (!$user) {
                $user = new User();
                $user->setMail($email);
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
            $this->addFlash('error', 'Une erreur s\'est produite lors de l\'authentification: ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        }
    }
}
