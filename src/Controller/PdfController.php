<?php

namespace App\Controller;

use App\Entity\Reward;
use App\Repository\RewardRepository;
use App\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PdfController extends AbstractController
{
    #[Route('/reward/pdf/{id}', name: 'app_reward_pdf')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function generateRewardPdf(
        Reward $reward,
        PdfService $pdfService
    ): Response
    {
        // Vérifier que l'utilisateur est bien l'auteur de l'avis
        if ($reward->getReview()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à accéder à cette récompense');
        }

        // Générer le PDF
        return $pdfService->generatePdf(
            'pdf/reward.html.twig',
            [
                'reward' => $reward,
                'user' => $this->getUser(),
                'date' => new \DateTime()
            ],
            'reduction_' . $reward->getPercentage() . '_pourcent.pdf'
        );
    }

    #[Route('/reward/saved/{id}', name: 'app_reward_saved')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function rewardSaved(
        Reward $reward
    ): Response
    {
        // Vérifier que l'utilisateur est bien l'auteur de l'avis
        if ($reward->getReview()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à accéder à cette récompense');
        }

        return $this->render('reward/saved.html.twig', [
            'reward' => $reward
        ]);
    }
}
