<?php

namespace App\Repository;

use App\Entity\ValorRiesgo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ValorRiesgo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ValorRiesgo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ValorRiesgo[]    findAll()
 * @method ValorRiesgo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ValorRiesgoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ValorRiesgo::class);
    }

    // /**
    //  * @return ValorRiesgo[] Returns an array of ValorRiesgo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ValorRiesgo
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
