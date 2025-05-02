<?php
namespace App\Controller;

use App\Service\HuggingFaceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class ChatbotController extends AbstractController
{
    #[Route('/chat', name: 'app_chat', methods: ['POST'])]
    public function chat(Request $request, HuggingFaceService $huggingFace, LoggerInterface $logger): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';

        if (empty($message)) {
            $logger->warning('Chatbot: Empty message received');
            return new JsonResponse(['error' => 'Message is required'], 400);
        }

        try {
            $prompt = "You are a travel assistant. Respond in English clearly and helpfully. The user asks: {$message}";
            $response = $huggingFace->generateResponse($prompt);
            if (str_starts_with($response, 'Error')) {
                $logger->error("Chatbot: $response");
            }
            return new JsonResponse(['response' => $response]);
        } catch (\Exception $e) {
            $logger->error("Chatbot: Error generating response: {$e->getMessage()}");
            return new JsonResponse(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
}