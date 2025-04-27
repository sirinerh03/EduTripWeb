<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

use Psr\Log\LoggerInterface;

class EmailService
{
    private $mailer;
    private $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function sendConfirmationEmail(string $to, string $subject, string $body): void
    {
        try {
            $email = (new Email())
                ->from('contact@edutrip.com')
                ->to($to)
                ->subject($subject)
                ->html($body);

            $this->mailer->send($email);
            $this->logger->info("Email envoyé à $to");
        } catch (\Exception $e) {
            $this->logger->error("Erreur d'envoi d'email: " . $e->getMessage());
        }
    }
}
