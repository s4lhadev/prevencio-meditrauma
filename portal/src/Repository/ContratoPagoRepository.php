<?php

namespace App\Repository;

use App\Entity\ContratoPago;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContratoPago|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoPago|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContratoPago[]    findAll()
 * @method ContratoPago[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoPagoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoPago::class);
    }

    // /**
    //  * @return ContratoPago[] Returns an array of ContratoPago objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ContratoPago
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
