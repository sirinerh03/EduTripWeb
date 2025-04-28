<?php

namespace App\Service;

use App\Entity\User;
use App\Service\EmailValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ApiEmailVerifier
{
    private $mailer;
    private $entityManager;
    private $router;
    private $params;
    private $httpClient;

    public function __construct(
        MailerInterface $mailer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $router,
        ParameterBagInterface $params,
        HttpClientInterface $httpClient
    ) {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->params = $params;
        $this->httpClient = $httpClient;
    }

    /**
     * Vérifie la validité d'une adresse email via le service EmailValidatorService
     */
    public function validateEmail(?string $email): array
    {
        $emailValidator = new EmailValidatorService($this->params, $this->httpClient);
        return $emailValidator->validateWithApi($email);
    }

    /**
     * Envoie un email de confirmation à l'utilisateur
     */
    public function sendEmailConfirmation(string $verifyEmailRouteName, UserInterface $user, TemplatedEmail $email): void
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('L\'utilisateur doit être une instance de App\Entity\User');
        }

        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        $user->setConfirmationToken($token);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Générer l'URL de vérification
        $verifyUrl = $this->router->generate(
            $verifyEmailRouteName,
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $context = $email->getContext();
        $context['signedUrl'] = $verifyUrl;
        $context['expiresAt'] = (new \DateTime())->modify('+24 hours');
        $context['user'] = $user;

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * Valide l'email de l'utilisateur à partir du token
     */
    public function handleEmailConfirmation(Request $request, UserInterface $user): bool
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('L\'utilisateur doit être une instance de App\Entity\User');
        }

        $token = $request->get('token');

        if (empty($token) || $token !== $user->getConfirmationToken()) {
            return false;
        }

        // Vérifier si le token n'est pas expiré (24h)
        $tokenCreatedAt = $user->getConfirmationTokenCreatedAt();
        if ($tokenCreatedAt && $tokenCreatedAt->modify('+24 hours') < new \DateTime()) {
            return false;
        }

        $user->setIsVerified(true);
        $user->setConfirmationToken(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return true;
    }
}
