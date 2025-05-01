<?php

namespace App\Repository;

use App\Entity\Candidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidature::class);
    }

    public function getCandidaturesByStatus()
    {
        return $this->createQueryBuilder('c')
            ->select('c.etat as status, COUNT(c.id) as count')
            ->groupBy('c.etat')
            ->getQuery()    
            ->getResult();
    }

    public function getTotalCandidatures()
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}