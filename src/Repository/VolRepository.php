<?php

namespace App\Repository;

use App\Entity\Vol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vol>
 *
 * @method Vol|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vol|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vol[]    findAll()
 * @method Vol[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vol::class);
    }

    public function save(Vol $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Vol $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAvailableFlights(string $depart, string $arrivee, \DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.depart = :depart')
            ->andWhere('v.arrivee = :arrivee')
            ->andWhere('v.dateDepart >= :date')
            ->andWhere('v.placesDisponibles > 0')
            ->setParameter('depart', $depart)
            ->setParameter('arrivee', $arrivee)
            ->setParameter('date', $date)
            ->orderBy('v.dateDepart', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 