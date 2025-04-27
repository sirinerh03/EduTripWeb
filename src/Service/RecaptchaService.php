<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;

class RecaptchaService
{
    private $params;
    private $secretKey;
    private $siteKey;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->secretKey = $this->params->get('recaptcha.secret_key');
        $this->siteKey = $this->params->get('recaptcha.site_key');
    }

    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    public function verify(string $recaptchaResponse, string $remoteIp = null): bool
    {
        if (empty($recaptchaResponse)) {
            return false;
        }

        $client = HttpClient::create();
        $response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $this->secretKey,
                'response' => $recaptchaResponse,
                'remoteip' => $remoteIp,
            ]
        ]);

        $data = $response->toArray();
        return $data['success'] ?? false;
    }
}
