<?php

namespace App\Repository;

use App\Entity\TarifaPrl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TarifaPrl|null find($id, $lockMode = null, $lockVersion = null)
 * @method TarifaPrl|null findOneBy(array $criteria, array $orderBy = null)
 * @method TarifaPrl[]    findAll()
 * @method TarifaPrl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TarifaPrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TarifaPrl::class);
    }

    // /**
    //  * @return TarifaPrl[] Returns an array of TarifaPrl objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TarifaPrl
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
