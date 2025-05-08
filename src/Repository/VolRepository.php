<?php
namespace App\Repository;

use App\Entity\Vol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vol::class);
    }

    public function findByFilters(?string $departure = null, ?string $arrival = null, ?\DateTimeInterface $date = null): array
    {
        $qb = $this->createQueryBuilder('v');

        if ($departure) {
            $qb->andWhere('v.aeroport_depart LIKE :departure')
               ->setParameter('departure', '%' . $departure . '%');
        }

        if ($arrival) {
            $qb->andWhere('v.aeroport_arrivee LIKE :arrival')
               ->setParameter('arrival', '%' . $arrival . '%');
        }

        if ($date) {
            $start = (clone $date)->setTime(0, 0, 0);
            $end = (clone $start)->modify('+1 day');

            $qb->andWhere('v.date_depart >= :startDate')
               ->andWhere('v.date_depart < :endDate')
               ->setParameter('startDate', $start)
               ->setParameter('endDate', $end);
        }

        return $qb->orderBy('v.date_depart', 'ASC')
                  ->getQuery()
                  ->getResult();
    }
}