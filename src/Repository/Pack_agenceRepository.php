<?php

namespace App\Repository;

use App\Entity\Pack_agence;  // Keep the entity name as Pack_agence (as per your requirement)
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Pack_agenceRepository extends ServiceEntityRepository  // Keep the repository class name in PascalCase
{
    public function __construct(ManagerRegistry $registry)
    {
        // Ensure the entity name is correctly referenced
        parent::__construct($registry, Pack_agence::class);  // Referencing the correct class
    }
   // src/Repository/PackAgenceRepository.php

   public function findWithFiltersAndSort(array $filters = [], string $sortField = 'date_ajout', string $direction = 'ASC'): array
{
    // Liste des champs valides pour le tri, avec correspondance aux propriétés exactes
    $validSortFields = ['nom_pk', 'prix', 'duree', 'date_ajout'];

    // Si le champ de tri n’est pas valide, on revient au champ par défaut
    if (!in_array($sortField, $validSortFields)) {
        $sortField = 'date_ajout';
    }

    $qb = $this->createQueryBuilder('p');

    // Filtres
    if (!empty($filters['nomPk'])) {
        $qb->andWhere('p.nom_pk LIKE :nomPk')
           ->setParameter('nomPk', '%' . $filters['nomPk'] . '%');
    }

    if (!empty($filters['prix'])) {
        $qb->andWhere('p.prix = :prix')
           ->setParameter('prix', $filters['prix']);
    }

    if (!empty($filters['duree'])) {
        $qb->andWhere('p.duree = :duree')
           ->setParameter('duree', $filters['duree']);
    }

    // Application du tri dynamique
    $qb->orderBy('p.' . $sortField, $direction);

    return $qb->getQuery()->getResult();
}

   

    
}