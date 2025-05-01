<?php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class RecaptchaService
{
    private $params;
    private $secretKey;
    private $siteKey;
    private $httpClient;
    private $logger;

    public function __construct(
        ParameterBagInterface $params,
        HttpClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->params = $params;
        $this->secretKey = $this->params->get('recaptcha.secret_key');
        $this->siteKey = $this->params->get('recaptcha.site_key');
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * Vérifie la réponse reCAPTCHA avec l'API Google
     */
    public function verify(string $recaptchaResponse, string $remoteIp = null): bool
    {
        if (empty($recaptchaResponse)) {
            return false;
        }

        try {
            // Utiliser cURL directement pour éviter les problèmes avec HttpClient
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'secret' => $this->secretKey,
                'response' => $recaptchaResponse,
                'remoteip' => $remoteIp,
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response === false) {
                $this->logger->error('reCAPTCHA cURL error');
                return false;
            }

            $data = json_decode($response, true);

            // Log pour le débogage
            $this->logger->info('reCAPTCHA response: ' . $response);

            if (isset($data['error-codes']) && !empty($data['error-codes'])) {
                $this->logger->warning('reCAPTCHA verification failed with errors: ' . json_encode($data['error-codes']));
            }

            return $data['success'] ?? false;
        } catch (\Exception $e) {
            $this->logger->error('reCAPTCHA verification error: ' . $e->getMessage());
            return false;
        }
    }
}
