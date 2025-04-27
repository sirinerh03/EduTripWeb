<?php

namespace App\Controller;

use App\Entity\SpinReward;
use App\Repository\SpinRewardRepository;
use App\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RewardController extends AbstractController
{
    #[Route('/reward/saved/{id}', name: 'app_reward_saved')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function rewardSaved(
        SpinReward $reward
    ): Response
    {
        return $this->render('reward/saved.html.twig', [
            'reward' => $reward
        ]);
    }

    #[Route('/reward/pdf/{id}', name: 'app_reward_pdf')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function generateRewardPdf(
        SpinReward $reward,
        PdfService $pdfService,
        SpinRewardRepository $spinRewardRepository
    ): Response
    {
        // Vérifier si l'utilisateur a droit à cette récompense
        $user = $this->getUser();
        $hasAccess = false;

        // Vérifier si l'utilisateur a un avis avec cette récompense
        foreach ($reward->getAvis() as $avis) {
            if ($avis->getUser() === $user) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à accéder à cette récompense.');
            return $this->redirectToRoute('app_avis_index');
        }

        // Générer le PDF
        return $pdfService->generatePdf(
            'pdf/reward.html.twig',
            [
                'reward' => $reward,
                'user' => $user,
                'date' => new \DateTime()
            ],
            'reduction_' . $reward->getPercentage() . '_pourcent.pdf'
        );
    }
}
