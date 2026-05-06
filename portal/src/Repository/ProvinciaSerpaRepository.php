<?php

namespace App\Repository;

use App\Entity\ProvinciaSerpa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProvinciaSerpa|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProvinciaSerpa|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProvinciaSerpa[]    findAll()
 * @method ProvinciaSerpa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProvinciaSerpaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProvinciaSerpa::class);
    }

    // /**
    //  * @return ProvinciaSerpa[] Returns an array of ProvinciaSerpa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProvinciaSerpa
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
