<?php

namespace App\Repository;

use App\Entity\Vol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vol>
 */
class VolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vol::class);
    }

    // src/Repository/VolRepository.php

public function findByFilters(?string $departure = null, ?string $arrival = null, ?\DateTimeInterface $date = null)
{
    $qb = $this->createQueryBuilder('v');
    
    if ($departure) {
        $qb->andWhere('v.aeroportDepart LIKE :departure')
           ->setParameter('departure', '%'.$departure.'%');
    }
    
    if ($arrival) {
        $qb->andWhere('v.aeroportArrivee LIKE :arrival')
           ->setParameter('arrival', '%'.$arrival.'%');
    }
    
    if ($date) {
        $qb->andWhere('v.dateDepart >= :startDate')
           ->andWhere('v.dateDepart < :endDate')
           ->setParameter('startDate', $date->format('Y-m-d 00:00:00'))
           ->setParameter('endDate', $date->modify('+1 day')->format('Y-m-d 00:00:00'));
    }
    
    return $qb->getQuery()->getResult();
}

    //    /**
    //     * @return Vol[] Returns an array of Vol objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vol
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
