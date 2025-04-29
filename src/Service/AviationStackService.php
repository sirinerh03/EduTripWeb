<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AviationStackService
{
    private $client;
    private $apiKey;

    public function __construct(HttpClientInterface $client, string $aviationstackApiKey)
    {
        $this->client = $client;
        $this->apiKey = $aviationstackApiKey;
    }

    public function getFlights(array $parameters = [])
    {
        try {
            $url = 'http://api.aviationstack.com/v1/flights';
            $params = array_merge(['access_key' => $this->apiKey], $parameters);
            $response = $this->client->request('GET', $url, ['query' => $params]);
            $result = $response->toArray();
    
            // ✅ On renvoie uniquement la partie 'data' du tableau
            return $result['data'] ?? [];
        } catch (\Exception $e) {
            return []; // évite les erreurs Twig en cas d'échec
        }
    }}