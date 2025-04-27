<?php

namespace App\Service;

use App\Entity\Avis;
use App\Entity\SpinReward;
use App\Repository\SpinRewardRepository;
use Doctrine\ORM\EntityManagerInterface;

class SpinRewardService
{
    private $spinRewardRepository;
    private $entityManager;

    public function __construct(
        SpinRewardRepository $spinRewardRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->spinRewardRepository = $spinRewardRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Attribue une récompense aléatoire à un avis
     */
    public function assignRandomReward(Avis $avis): ?SpinReward
    {
        // Vérifier si l'avis a déjà une récompense
        if ($avis->getSpinReward() !== null) {
            return $avis->getSpinReward();
        }

        // Récupérer une récompense aléatoire
        $reward = $this->spinRewardRepository->findRandomActive();
        
        if ($reward) {
            $avis->setSpinReward($reward);
            $this->entityManager->persist($avis);
            $this->entityManager->flush();
        }
        
        return $reward;
    }

    /**
     * Marque une récompense comme réclamée
     */
    public function claimReward(Avis $avis): bool
    {
        if ($avis->getSpinReward() === null || $avis->isRewardClaimed()) {
            return false;
        }
        
        $avis->setRewardClaimed(true);
        $this->entityManager->persist($avis);
        $this->entityManager->flush();
        
        return true;
    }
}
