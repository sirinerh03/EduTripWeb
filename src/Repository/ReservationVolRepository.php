<?php

namespace App\Repository;

use App\Entity\ReservationVol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReservationVol>
 */
class ReservationVolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationVol::class);
    }



    public function countReservationsByDestination(): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('v.aeroport_arrivee', 'COUNT(r.id) as count')
            ->join('r.vol', 'v')
            ->groupBy('v.aeroport_arrivee');
    
        return $qb->getQuery()->getResult();
    }
    //    /**
    //     * @return ReservationVol[] Returns an array of ReservationVol objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ReservationVol
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}