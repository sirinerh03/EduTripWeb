<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CaptchaService
{
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    /**
     * Génère un code captcha aléatoire et le stocke en session
     */
    public function generateCaptchaCode(int $length = 6): string
    {
        // Caractères autorisés pour le captcha (sans caractères ambigus comme 0, O, 1, I, etc.)
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $captchaCode = '';

        $max = strlen($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $captchaCode .= $characters[mt_rand(0, $max)];
        }

        // Stocker le code en session
        $this->session->set('captcha_code', $captchaCode);

        return $captchaCode;
    }

    /**
     * Vérifie si le code captcha fourni correspond à celui en session
     */
    public function validateCaptchaCode(string $userInput): bool
    {
        $captchaCode = $this->session->get('captcha_code');

        // Vérifier que le code existe en session
        if (!$captchaCode) {
            // Si le code n'existe pas en session, on génère un nouveau code
            // Cela peut arriver si la session a expiré
            $this->generateCaptchaCode();
            return false;
        }

        // Comparaison insensible à la casse
        $isValid = strtoupper($userInput) === strtoupper($captchaCode);

        // Si le code est valide, on le supprime de la session pour éviter qu'il soit réutilisé
        if ($isValid) {
            $this->session->remove('captcha_code');
        }

        return $isValid;
    }
}
