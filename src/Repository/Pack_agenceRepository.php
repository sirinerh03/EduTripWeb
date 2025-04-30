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

    // Example of a custom method if you need one
}
