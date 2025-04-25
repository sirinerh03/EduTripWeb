<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class BadWordsChecker
{
    private const API_URL = 'https://bad-words-filter-spi.p.rapidapi.com/badwords';
    private const API_HOST = 'bad-words-filter-spi.p.rapidapi.com';
    private const TIMEOUT = 3;

    public function __construct(
        private HttpClientInterface $client,
        private string $apiKey,
        private ?LoggerInterface $logger = null
    ) {}

    public function filterText(string $text): array
    {
        try {
            $response = $this->client->request(
                'POST',
                self::API_URL,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'X-RapidAPI-Host' => self::API_HOST,
                        'X-RapidAPI-Key' => $this->apiKey,
                    ],
                    'json' => ['text' => $text],
                    'timeout' => self::TIMEOUT
                ]
            );

            return $response->toArray();

        } catch (\Exception $e) {
            $this->logger?->error('BadWords Filter Error', [
                'message' => $e->getMessage(),
                'text' => substr($text, 0, 50)
            ]);
            return ['error' => $e->getMessage()];
        }
    }
}