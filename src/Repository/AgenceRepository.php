<?php

// src/Repository/AgenceRepository.php
namespace App\Repository;

use App\Entity\Agence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class AgenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agence::class);
    }
   // Dans le repository
   // src/Repository/AgenceRepository.php

  // In AgenceRepository.php
public function findWithFiltersAndSort(array $filters = [], $sortField = 'date_creation', $direction = 'ASC')
{
    // Liste des champs valides pour le tri
    $validSortFields = ['date_creation', 'nom_ag', 'telephone_ag', 'adresse_ag', 'email_ag', 'description_ag'];

    // Vérification du champ de tri
    if (!in_array($sortField, $validSortFields)) {
        $sortField = 'date_creation';  // Valeur par défaut pour le tri
    }

    $qb = $this->createQueryBuilder('a');

    // Apply filters if they exist
    if (!empty($filters['nom_ag'])) {
        $qb->andWhere('a.nom_ag LIKE :nomAg')
           ->setParameter('nomAg', '%' . $filters['nom_ag'] . '%');
    }

    if (!empty($filters['adresse_ag'])) {
        $qb->andWhere('a.adresse_ag LIKE :adresseAg')
           ->setParameter('adresseAg', '%' . $filters['adresse_ag'] . '%');
    }

    // Apply sorting
    $qb->orderBy('a.' . $sortField, $direction);

    return $qb->getQuery()->getResult();
}
   


   

}
