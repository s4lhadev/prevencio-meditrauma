<?php

namespace App\Repository;

use App\Entity\FacturacionVencimiento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FacturacionVencimiento|null find($id, $lockMode = null, $lockVersion = null)
 * @method FacturacionVencimiento|null findOneBy(array $criteria, array $orderBy = null)
 * @method FacturacionVencimiento[]    findAll()
 * @method FacturacionVencimiento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FacturacionVencimientoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FacturacionVencimiento::class);
    }

    // /**
    //  * @return FacturacionVencimiento[] Returns an array of FacturacionVencimiento objects
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
    public function findOneBySomeField($value): ?FacturacionVencimiento
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
