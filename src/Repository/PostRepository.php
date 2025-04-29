<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    //    /**
    //     * @return Post[] Returns an array of Post objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findWithSearch(?string $term)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.utilisateur', 'u')
            ->addSelect('u')
            ->where('p.contenu LIKE :term OR u.nom LIKE :term OR u.prenom LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('p.date_creation', 'DESC')
            ->getQuery();
    }
    
    public function findWithFilter(string $categorie)
    {
        return $this->createQueryBuilder('p')
            ->where('p.categorie = :cat')
            ->setParameter('cat', $categorie)
            ->orderBy('p.date_creation', 'DESC')
            ->getQuery();
    }
    
    public function findAllPosts()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.date_creation', 'DESC')
            ->getQuery();
    }
    
    public function findAllCategories(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('DISTINCT p.categorie');
    
        return array_map(fn($row) => $row['categorie'], $qb->getQuery()->getArrayResult());
    }

// src/Repository/PostRepository.php
public function getPostPlusLike(): ?Post
{
    return $this->createQueryBuilder('p')
        ->orderBy('p.likes', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}

}