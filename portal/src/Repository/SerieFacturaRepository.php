<?php

namespace App\Repository;

use App\Entity\SerieFactura;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SerieFactura|null find($id, $lockMode = null, $lockVersion = null)
 * @method SerieFactura|null findOneBy(array $criteria, array $orderBy = null)
 * @method SerieFactura[]    findAll()
 * @method SerieFactura[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SerieFacturaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SerieFactura::class);
    }

    // /**
    //  * @return SerieFactura[] Returns an array of SerieFactura objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SerieFactura
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
