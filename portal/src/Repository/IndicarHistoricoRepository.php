<?php

namespace App\Repository;

use App\Entity\IndicarHistorico;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IndicarHistorico|null find($id, $lockMode = null, $lockVersion = null)
 * @method IndicarHistorico|null findOneBy(array $criteria, array $orderBy = null)
 * @method IndicarHistorico[]    findAll()
 * @method IndicarHistorico[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndicarHistoricoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IndicarHistorico::class);
    }

    // /**
    //  * @return IndicarHistorico[] Returns an array of IndicarHistorico objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IndicarHistorico
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
