<?php

namespace App\Repository;

use App\Entity\BuscadorQueries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BuscadorQueries|null find($id, $lockMode = null, $lockVersion = null)
 * @method BuscadorQueries|null findOneBy(array $criteria, array $orderBy = null)
 * @method BuscadorQueries[]    findAll()
 * @method BuscadorQueries[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BuscadorQueriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BuscadorQueries::class);
    }

    // /**
    //  * @return BuscadorQueries[] Returns an array of BuscadorQueries objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BuscadorQueries
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
