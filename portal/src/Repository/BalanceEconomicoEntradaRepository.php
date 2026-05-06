<?php

namespace App\Repository;

use App\Entity\BalanceEconomicoEntrada;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BalanceEconomicoEntrada|null find($id, $lockMode = null, $lockVersion = null)
 * @method BalanceEconomicoEntrada|null findOneBy(array $criteria, array $orderBy = null)
 * @method BalanceEconomicoEntrada[]    findAll()
 * @method BalanceEconomicoEntrada[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BalanceEconomicoEntradaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BalanceEconomicoEntrada::class);
    }

    // /**
    //  * @return BalanceEconomicoEntrada[] Returns an array of BalanceEconomicoEntrada objects
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
    public function findOneBySomeField($value): ?BalanceEconomicoEntrada
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
