<?php
namespace App\Service;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BadWordsService
{
    private string $apiKey;
    private HttpClientInterface $client;

    public function __construct(string $apiKey, HttpClientInterface $client)
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
    }

    public function checkText(string $text): bool
    {
        // Exemple d'appel vers ton API de bad words
        $response = $this->client->request('POST', 'https://api.api-ninjas.com/v1/badwords', [
            'headers' => [
                'X-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode(['text' => $text]),
        ]);

        $data = $response->toArray();

        // Exemple : si "bad_words" est vide, il n'y a pas de mots interdits
        return empty($data['bad_words']);
    }
    public function containsBadWords(string $text): bool
{
    $response = $this->client->request('POST', 'https://api.api-ninjas.com/v1/badwords', [
        'headers' => [
            'X-Api-Key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode(['text' => $text]),
    ]);

    $data = $response->toArray();
    return !empty($data['bad_words']); // Note the negation here
}
}
