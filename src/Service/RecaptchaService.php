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
            $response = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $this->secretKey,
                    'response' => $recaptchaResponse,
                    'remoteip' => $remoteIp,
                ]
            ]);

            $data = $response->toArray();

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
