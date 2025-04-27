<?php

namespace App\Controller;

use App\Service\CaptchaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CaptchaController extends AbstractController
{
    #[Route('/captcha/generate', name: 'app_captcha_generate')]
    public function generate(CaptchaService $captchaService): Response
    {
        // Générer un nouveau code captcha
        $captchaCode = $captchaService->generateCaptchaCode();

        // Renvoyer le template avec le code captcha
        return $this->render('captcha/text_captcha.html.twig', [
            'captcha_code' => $captchaCode
        ]);
    }
}
