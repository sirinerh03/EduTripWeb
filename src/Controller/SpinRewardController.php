<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\SpinReward;
use App\Repository\AvisRepository;
use App\Service\SpinRewardService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/spin-reward')]
class SpinRewardController extends AbstractController
{
    private $spinRewardService;
    private $avisRepository;
    private $logger;

    public function __construct(
        SpinRewardService $spinRewardService,
        AvisRepository $avisRepository,
        LoggerInterface $logger
    ) {
        $this->spinRewardService = $spinRewardService;
        $this->avisRepository = $avisRepository;
        $this->logger = $logger;
    }

    #[Route('/spin/{id}', name: 'app_spin_reward')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function spin(Avis $avis): Response
    {
        // Vérifier que l'utilisateur est bien l'auteur de l'avis
        if ($avis->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à accéder à cette récompense.');
            return $this->redirectToRoute('app_avis_index');
        }

        // Vérifier si l'avis a déjà une récompense réclamée
        if ($avis->isRewardClaimed()) {
            $this->addFlash('info', 'Vous avez déjà réclamé votre récompense pour cet avis.');
            return $this->redirectToRoute('app_avis_show', ['id' => $avis->getId()]);
        }

        // Attribuer une récompense aléatoire si ce n'est pas déjà fait
        $reward = $this->spinRewardService->assignRandomReward($avis);

        if (!$reward) {
            $this->addFlash('error', 'Aucune récompense disponible pour le moment.');
            return $this->redirectToRoute('app_avis_show', ['id' => $avis->getId()]);
        }

        return $this->render('spin_reward/spin.html.twig', [
            'avis' => $avis,
            'reward' => $reward
        ]);
    }

    #[Route('/claim/{id}', name: 'app_claim_reward')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function claim(Avis $avis): Response
    {
        // Vérifier que l'utilisateur est bien l'auteur de l'avis
        if ($avis->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à réclamer cette récompense.');
            return $this->redirectToRoute('app_avis_index');
        }

        // Vérifier si l'avis a une récompense
        if ($avis->getSpinReward() === null) {
            $this->addFlash('error', 'Aucune récompense à réclamer pour cet avis.');
            return $this->redirectToRoute('app_avis_show', ['id' => $avis->getId()]);
        }

        // Réclamer la récompense
        $claimed = $this->spinRewardService->claimReward($avis);

        if ($claimed) {
            $this->addFlash('success', 'Félicitations ! Votre réduction de ' . $avis->getSpinReward()->getPercentage() . '% a été appliquée à votre compte.');
        } else {
            $this->addFlash('info', 'Cette récompense a déjà été réclamée.');
        }

        return $this->redirectToRoute('app_avis_show', ['id' => $avis->getId()]);
    }

    #[Route('/save-reward', name: 'app_save_spin_reward', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function saveReward(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            // Récupérer les données de la requête
            $data = json_decode($request->getContent(), true);

            // Log pour déboguer
            $this->logger->info('Données reçues pour la sauvegarde de récompense', [
                'data' => $data,
                'user_id' => $this->getUser()->getId()
            ]);

            if (!isset($data['reward'])) {
                $this->logger->error('Données manquantes pour la sauvegarde de récompense');
                return $this->json(['success' => false, 'message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
            }

            // Récupérer l'utilisateur connecté
            $user = $this->getUser();

            // Vérifier que l'utilisateur est connecté
            if (!$user) {
                $this->logger->error('Utilisateur non connecté lors de la sauvegarde de récompense');
                return $this->json(['success' => false, 'message' => 'Vous devez être connecté pour sauvegarder une récompense'], Response::HTTP_UNAUTHORIZED);
            }

            // Vérifier si une récompense avec ce pourcentage existe déjà
            $existingReward = $entityManager->getRepository(SpinReward::class)->findOneBy(['percentage' => (int)$data['reward']]);

            if ($existingReward) {
                $reward = $existingReward;
                $this->logger->info('Récompense existante utilisée', [
                    'reward_id' => $reward->getId(),
                    'percentage' => $reward->getPercentage()
                ]);
            } else {
                // Créer une nouvelle récompense
                $reward = new SpinReward();
                $reward->setPercentage((int)$data['reward']); // Conversion explicite en entier
                $reward->setDescription('Réduction de ' . $data['reward'] . '% sur votre prochain voyage éducatif');
                $reward->setIsActive(true);

                // Sauvegarder la récompense
                $entityManager->persist($reward);
                $entityManager->flush();

                $this->logger->info('Nouvelle récompense créée', [
                    'reward_id' => $reward->getId(),
                    'percentage' => $reward->getPercentage()
                ]);
            }

            // Associer la récompense à l'utilisateur via un avis
            $avis = $this->avisRepository->findOneBy(['user' => $user], ['createdAt' => 'DESC']);

            if ($avis) {
                $avis->setSpinReward($reward);
                $avis->setRewardClaimed(true);
                $entityManager->persist($avis);
                $entityManager->flush();

                $this->logger->info('Récompense associée à un avis', [
                    'reward_id' => $reward->getId(),
                    'avis_id' => $avis->getId(),
                    'user_id' => $user->getId()
                ]);
            } else {
                $this->logger->warning('Aucun avis trouvé pour associer la récompense', [
                    'user_id' => $user->getId()
                ]);
            }

            // Ajouter un message flash
            $this->addFlash('success', 'Votre réduction de ' . $data['reward'] . '% a été sauvegardée avec succès !');

            $this->logger->info('Récompense sauvegardée avec succès', [
                'reward_id' => $reward->getId(),
                'percentage' => $reward->getPercentage()
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Récompense sauvegardée avec succès',
                'rewardId' => $reward->getId(),
                'percentage' => $reward->getPercentage()
            ]);
        } catch (\Exception $e) {
            // Log l'erreur
            $this->logger->error('Erreur lors de la sauvegarde de la récompense', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
