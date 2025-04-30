<?php

namespace App\Repository;

use App\Entity\Hebergement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hebergement>
 */
class HebergementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hebergement::class);
    }

    public function searchByCriteria(array $criteria): array
{
    $qb = $this->createQueryBuilder('h');
    
    // Existing filter conditions...
    if (!empty($criteria['nom'])) {
        $qb->andWhere('h.nomh LIKE :nom')
           ->setParameter('nom', '%'.$criteria['nom'].'%');
    }
    
    if (!empty($criteria['type'])) {
        $qb->andWhere('h.typeh = :type')
           ->setParameter('type', $criteria['type']);
    }
    
    if (!empty($criteria['min_capacity'])) {
        $qb->andWhere('h.capaciteh >= :min_cap')
           ->setParameter('min_cap', $criteria['min_capacity']);
    }
    
    if (!empty($criteria['max_price'])) {
        $qb->andWhere('h.prixh <= :max_price')
           ->setParameter('max_price', $criteria['max_price']);
    }
    
    if (!empty($criteria['disponibilite'])) {
        $qb->andWhere('h.disponibleh = :dispo')
           ->setParameter('dispo', $criteria['disponibilite']);
    }
    
    // Handle sorting for AJAX requests
    if (!empty($criteria['sort'])) {
        switch ($criteria['sort']) {
            case 'price_asc':
                $qb->orderBy('h.prixh', 'ASC');
                break;
            case 'price_desc':
                $qb->orderBy('h.prixh', 'DESC');
                break;
            case 'capacity_asc':
                $qb->orderBy('h.capaciteh', 'ASC');
                break;
            case 'capacity_desc':
                $qb->orderBy('h.capaciteh', 'DESC');
                break;
            case 'name_asc':
                $qb->orderBy('h.nomh', 'ASC');
                break;
            case 'name_desc':
                $qb->orderBy('h.nomh', 'DESC');
                break;
        }
    }
    
    return $qb->getQuery()->getResult();
}
public function getPaginatedQueryBuilder(array $criteria): \Doctrine\ORM\QueryBuilder
{
    $qb = $this->createQueryBuilder('h');
    
    // Apply filters (if any)
    if (!empty($criteria['nom'])) {
        $qb->andWhere('h.nomh LIKE :nom')
           ->setParameter('nom', '%'.$criteria['nom'].'%');
    }
    
    if (!empty($criteria['type'])) {
        $qb->andWhere('h.typeh = :type')
           ->setParameter('type', $criteria['type']);
    }
    
    if (!empty($criteria['min_capacity'])) {
        $qb->andWhere('h.capaciteh >= :min_cap')
           ->setParameter('min_cap', $criteria['min_capacity']);
    }
    
    if (!empty($criteria['max_price'])) {
        $qb->andWhere('h.prixh <= :max_price')
           ->setParameter('max_price', $criteria['max_price']);
    }
    
    if (!empty($criteria['disponibilite'])) {
        $qb->andWhere('h.disponibleh = :dispo')
           ->setParameter('dispo', $criteria['disponibilite']);
    }
    
    // Apply sorting
    if (!empty($criteria['sort'])) {
        switch ($criteria['sort']) {
            case 'price_asc':
                $qb->orderBy('h.prixh', 'ASC');
                break;
            case 'price_desc':
                $qb->orderBy('h.prixh', 'DESC');
                break;
            case 'capacity_asc':
                $qb->orderBy('h.capaciteh', 'ASC');
                break;
            case 'capacity_desc':
                $qb->orderBy('h.capaciteh', 'DESC');
                break;
            case 'name_asc':
                $qb->orderBy('h.nomh', 'ASC');
                break;
            case 'name_desc':
                $qb->orderBy('h.nomh', 'DESC');
                break;
        }
    } else {
        $qb->orderBy('h.prixh', 'ASC'); // Default sorting
    }
    
    return $qb;
}
    //    /**
    //     * @return Hebergement[] Returns an array of Hebergement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('h.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Hebergement
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    // src/Repository/HebergementRepository.php

public function findAllWithReservations(): array
{
    return $this->createQueryBuilder('h')
        ->leftJoin('h.reservations', 'r')
        ->addSelect('r')
        ->getQuery()
        ->getResult();
}
}
