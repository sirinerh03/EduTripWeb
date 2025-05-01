<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    private VerifyEmailHelperInterface $verifyEmailHelper;
    private MailerInterface $mailer;
    private UrlGeneratorInterface $router;

    public function __construct(VerifyEmailHelperInterface $helper, MailerInterface $mailer, UrlGeneratorInterface $router)
    {
        $this->verifyEmailHelper = $helper;
        $this->mailer = $mailer;
        $this->router = $router;
    }

    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, TemplatedEmail $email): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getIdUser(),
            $user->getMail(),
            ['id' => $user->getIdUser()]
        );

        $email->context([
            'signedUrl' => $signatureComponents->getSignedUrl(),
            'expiresAt' => $signatureComponents->getExpiresAt(),
            'user' => $user,
        ]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new \Exception('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
        }
    }
}
