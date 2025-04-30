<?php

namespace App\Repository;

use App\Entity\SpinReward;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SpinReward>
 *
 * @method SpinReward|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpinReward|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpinReward[]    findAll()
 * @method SpinReward[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpinRewardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpinReward::class);
    }

    /**
     * Récupère toutes les récompenses actives
     * 
     * @return SpinReward[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère une récompense aléatoire parmi les actives
     * 
     * @return SpinReward|null
     */
    public function findRandomActive(): ?SpinReward
    {
        $activeRewards = $this->findAllActive();
        
        if (empty($activeRewards)) {
            return null;
        }
        
        $randomIndex = array_rand($activeRewards);
        return $activeRewards[$randomIndex];
    }
}
