<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\Reward;
use App\Repository\RewardRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SaveRewardController extends AbstractController
{
    #[Route('/save-reward', name: 'app_save_reward', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function saveReward(
        Request $request,
        EntityManagerInterface $entityManager,
        ReviewRepository $reviewRepository
    ): JsonResponse
    {
        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);

        if (!isset($data['reward']) || !isset($data['reviewId'])) {
            return new JsonResponse(['success' => false, 'message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer l'avis
        $review = $reviewRepository->find($data['reviewId']);

        if (!$review) {
            return new JsonResponse(['success' => false, 'message' => 'Avis non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est bien l'auteur de l'avis
        if ($review->getUser() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à réclamer cette récompense'], Response::HTTP_FORBIDDEN);
        }

        // Créer une nouvelle récompense
        $reward = new Reward();
        $reward->setPercentage($data['reward']);
        $reward->setDescription('Réduction de ' . $data['reward'] . '% sur votre prochain voyage éducatif');
        $reward->setReview($review);
        $reward->setIsClaimed(true);
        $reward->setClaimedAt(new \DateTimeImmutable());

        // Sauvegarder la récompense
        $entityManager->persist($reward);
        $entityManager->flush();

        // Stocker l'ID de la récompense dans la session pour la page de confirmation
        $request->getSession()->set('last_reward_id', $reward->getId());

        return new JsonResponse([
            'success' => true,
            'message' => 'Récompense sauvegardée avec succès',
            'rewardId' => $reward->getId()
        ]);
    }

    #[Route('/reward/confirmation', name: 'app_reward_confirmation')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function confirmationPage(
        Request $request,
        RewardRepository $rewardRepository
    ): Response
    {
        // Récupérer l'ID de la récompense depuis la session
        $rewardId = $request->getSession()->get('last_reward_id');

        if (!$rewardId) {
            $this->addFlash('error', 'Aucune récompense trouvée.');
            return $this->redirectToRoute('app_review_index');
        }

        // Récupérer la récompense
        $reward = $rewardRepository->find($rewardId);

        if (!$reward) {
            $this->addFlash('error', 'Récompense non trouvée.');
            return $this->redirectToRoute('app_review_index');
        }

        // Vérifier que l'utilisateur est bien l'auteur de l'avis
        if ($reward->getReview()->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à accéder à cette récompense.');
            return $this->redirectToRoute('app_review_index');
        }

        return $this->render('reward/confirmation.html.twig', [
            'reward' => $reward
        ]);
    }
}
