<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // ajoutez ici vos méthodes personnalisées si besoin

    /**
     * Recherche des utilisateurs en fonction de critères
     *
     * @param array $criteria Les critères de recherche
     * @return User[] Les utilisateurs correspondants
     */
    public function searchUsers(array $criteria = []): array
    {
        $qb = $this->createQueryBuilder('u');

        // Recherche par nom ou prénom
        if (!empty($criteria['search'])) {
            $search = $criteria['search'];
            $qb->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.mail LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Filtre par rôle
        if (!empty($criteria['role'])) {
            $qb->andWhere('u.role = :role')
               ->setParameter('role', $criteria['role']);
        }

        // Filtre par statut
        if (!empty($criteria['status'])) {
            $qb->andWhere('u.status = :status')
               ->setParameter('status', $criteria['status']);
        }

        // Tri
        $sortField = $criteria['sort_field'] ?? 'id';
        $sortOrder = $criteria['sort_order'] ?? 'DESC';

        // Vérifier que le champ de tri est valide
        if (!property_exists(User::class, $sortField)) {
            $sortField = 'id';
        }

        $qb->orderBy('u.' . $sortField, $sortOrder);

        return $qb->getQuery()->getResult();
    }
}
