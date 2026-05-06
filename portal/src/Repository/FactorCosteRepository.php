<?php

namespace App\Repository;

use App\Entity\FactorCoste;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FactorCoste|null find($id, $lockMode = null, $lockVersion = null)
 * @method FactorCoste|null findOneBy(array $criteria, array $orderBy = null)
 * @method FactorCoste[]    findAll()
 * @method FactorCoste[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FactorCosteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FactorCoste::class);
    }

    // /**
    //  * @return FactorCoste[] Returns an array of FactorCoste objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FactorCoste
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
