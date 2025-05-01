<?php
namespace App\Service;
use App\Entity\Post;  
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FacebookShareService
{
    private $client;
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->client = HttpClient::create();
        $this->params = $params;
    }

    public function generateAISuggestion(string $content): string
    {
        // Exemple simple d'IA (en production, utilisez une API comme OpenAI)
        $keyPhrases = explode(' ', $content);
        $keyPhrases = array_slice(array_unique($keyPhrases), 0, 3);
        
        return "Découvrez ce post intéressant sur " . implode(', ', $keyPhrases) . "...";
    }

    public function getFacebookLoginUrl(): string
    {
        $appId = $this->params->get('facebook.app_id');
        $redirectUri = $this->params->get('facebook.redirect_uri');

        return sprintf(
            'https://www.facebook.com/v12.0/dialog/oauth?client_id=%s&redirect_uri=%s&state={st=state123abc,ds=123456789}&scope=pages_manage_posts,publish_to_groups',
            $appId,
            urlencode($redirectUri)
        );
    }

    public function shareOnFacebook(string $accessToken, Post $post): array
    {
        $aiMessage = $this->generateAISuggestion($post->getContenu());
        
        $response = $this->client->request('POST', 'https://graph.facebook.com/v12.0/me/feed', [
            'query' => [
                'access_token' => $accessToken,
                'message' => $aiMessage,
                'link' => 'https://votresite.com/post/'.$post->getIdPost(),
            ],
        ]);

        return $response->toArray();
    }
    // src/Service/FacebookShareService.php

    public function exchangeCodeForToken(string $code): array
    {
        $response = $this->client->request('GET', 'https://graph.facebook.com/v12.0/oauth/access_token', [
            'query' => [
                'client_id' => $this->params->get('facebook.app_id'),
                'client_secret' => $this->params->get('facebook.app_secret'),
                'redirect_uri' => $this->params->get('facebook.redirect_uri'),
                'code' => $code
            ]
        ]);
    
        $data = $response->toArray();
    
        if (isset($data['error'])) {
            throw new \Exception($data['error']['message'] ?? 'Facebook API error');
        }
    
        if (!isset($data['access_token'])) {
            throw new \Exception('Access token not found in Facebook response');
        }
    
        return $data;
    }
}