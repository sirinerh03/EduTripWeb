<?php

namespace App\Repository;

use App\Entity\University;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UniversityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, University::class);
    }

    /**
     * Returns a QueryBuilder for advanced search and sort
     */
    public function getUniversitiesQueryBuilder(?string $search, ?string $sort, ?string $direction = 'ASC')
    {
        $qb = $this->createQueryBuilder('u');
        if ($search) {
            $qb->andWhere('LOWER(u.nom) LIKE :search OR LOWER(u.ville) LIKE :search')
                ->setParameter('search', '%' . strtolower($search) . '%');
        }
        $allowedSortFields = ['nom', 'ville', 'email'];
        $sortField = in_array($sort, $allowedSortFields) ? $sort : 'nom';
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy('u.' . $sortField, $dir);
        return $qb;
    }

    public function getUniversitiesByCity()
    {
        return $this->createQueryBuilder('u')
            ->select('u.ville as city, COUNT(u.id) as count')
            ->groupBy('u.ville')
            ->getQuery()
            ->getResult();
    }

    public function getCandidaturesPerUniversity()
    {
        return $this->createQueryBuilder('u')
            ->select('u.nom as university, COUNT(c.id) as count')
            ->leftJoin('u.candidatures', 'c')
            ->groupBy('u.id, u.nom')
            ->getQuery()
            ->getResult();
    }

    public function getTotalUniversities()
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

}
