<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendConfirmationEmail(string $to, string $subject, string $body): void
    {
        try {
            $email = (new Email())
                ->from('edutrip.edutrip@gmail.com')
                ->to($to)
                ->subject($subject)
                ->html($body);
    
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Loguer l'erreur pour le débogage
            error_log('Erreur lors de l’envoi de l’email : ' . $e->getMessage());
            throw new \Exception('Échec de l’envoi de l’email : ' . $e->getMessage());
        }
    } }
