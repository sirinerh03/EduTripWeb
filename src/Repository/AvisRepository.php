<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Avis;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avis>
 *
 * @method Avis|null find($id, $lockMode = null, $lockVersion = null)
 * @method Avis|null findOneBy(array $criteria, array $orderBy = null)
 * @method Avis[]    findAll()
 * @method Avis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    public function save(Avis $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Avis $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestAvis(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // ajoutez ici vos méthodes personnalisées si besoin

    /**
     * Recherche des avis en fonction de critères
     *
     * @param array $criteria Les critères de recherche
     * @return Avis[] Les avis correspondants
     */
    public function searchAvis(array $criteria = []): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.user', 'u')
            ->addSelect('u');

        // Recherche par contenu
        if (!empty($criteria['search'])) {
            $search = $criteria['search'];
            $qb->andWhere('a.comment LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Filtre par note
        if (!empty($criteria['rating'])) {
            $qb->andWhere('a.rating = :rating')
               ->setParameter('rating', $criteria['rating']);
        }

        // Filtre par utilisateur
        if (!empty($criteria['user_id'])) {
            $qb->andWhere('a.user = :user_id')
               ->setParameter('user_id', $criteria['user_id']);
        }

        // Filtre par date (période)
        if (!empty($criteria['date_from'])) {
            $qb->andWhere('a.createdAt >= :date_from')
               ->setParameter('date_from', new \DateTimeImmutable($criteria['date_from']));
        }

        if (!empty($criteria['date_to'])) {
            $qb->andWhere('a.createdAt <= :date_to')
               ->setParameter('date_to', new \DateTimeImmutable($criteria['date_to']));
        }

        // Tri
        $sortField = $criteria['sort_field'] ?? 'createdAt';
        $sortOrder = $criteria['sort_order'] ?? 'DESC';

        // Vérifier que le champ de tri est valide
        if (!property_exists(Avis::class, $sortField)) {
            $sortField = 'createdAt';
        }

         $qb->andWhere('a.user IS NULL OR u.id IS NOT NULL');

        return $qb->getQuery()->getResult();
    }
}
