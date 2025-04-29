<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private HttpClientInterface $httpClient;
    private string $apikey;

    public function __construct(HttpClientInterface $httpClient, string $apikey)
    {
        $this->httpClient = $httpClient;
        $this->apikey = $apikey;
    }

    public function getWeather(string $city): array
    {
        $response = $this->httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
            'query' => [
                'q' => $city,
                'appid' => $this->apikey,
                'units' => 'metric',
                'lang' => 'fr',
            ],
        ]);

        return $response->toArray();
    }
}