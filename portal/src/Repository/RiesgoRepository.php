<?php

namespace App\Repository;

use App\Entity\Riesgo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Riesgo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Riesgo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Riesgo[]    findAll()
 * @method Riesgo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RiesgoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Riesgo::class);
    }

    // /**
    //  * @return Riesgo[] Returns an array of Riesgo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Riesgo
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
