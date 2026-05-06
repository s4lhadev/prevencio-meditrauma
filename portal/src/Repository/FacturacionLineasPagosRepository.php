<?php

namespace App\Repository;

use App\Entity\FacturacionLineasPagos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FacturacionLineasPagos|null find($id, $lockMode = null, $lockVersion = null)
 * @method FacturacionLineasPagos|null findOneBy(array $criteria, array $orderBy = null)
 * @method FacturacionLineasPagos[]    findAll()
 * @method FacturacionLineasPagos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FacturacionLineasPagosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FacturacionLineasPagos::class);
    }

    // /**
    //  * @return FacturacionLineasPagos[] Returns an array of FacturacionLineasPagos objects
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
    public function findOneBySomeField($value): ?FacturacionLineasPagos
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
