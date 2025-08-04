<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

//    /**
//     * @return Article[] Returns an array of Article objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findBySearch(string $value): array
{
    return $this->createQueryBuilder('a')
        ->where('(LOWER(a.titre) LIKE LOWER(:val) 
               OR LOWER(a.chapeau) LIKE LOWER(:val) 
               OR LOWER(a.texte) LIKE LOWER(:val))')
        ->andWhere('a.publier = :publier')
        ->setParameter('val', '%' . $value . '%')
        ->setParameter('publier', true)
        ->orderBy('a.date_crea', 'DESC')
        ->getQuery()
        ->getResult();
}
}