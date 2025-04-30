<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HuggingFaceService
{
    public function __construct(
        private HttpClientInterface $client,
        private string $apiKey
    ) {}

    public function generateResponse(string $prompt): string
    {
        try {
            if (empty($this->apiKey) || $this->apiKey === '%env(HUGGINGFACE_API_KEY)%') {
                throw new \Exception('Hugging Face API key not configured');
            }

            $response = $this->client->request('POST', 
                'https://api-inference.huggingface.co/models/mistralai/Mixtral-8x7B-Instruct-v0.1',
                [
                    'headers' => [
                        'Authorization' => "Bearer {$this->apiKey}",
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'inputs' => $prompt,
                        'parameters' => [
                            'max_new_tokens' => 250,
                            'return_full_text' => false,
                            'temperature' => 0.7,
                        ],
                    ],
                    'timeout' => 30,
                ]
            );

            $data = $response->toArray();
            return trim($data[0]['generated_text'] ?? 'Error: No response generated');
        } catch (\Exception $e) {
            return 'Error Hugging Face: ' . $e->getMessage();
        }
    }
}