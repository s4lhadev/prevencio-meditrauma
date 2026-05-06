<?php

namespace App\Repository;

use App\Entity\SmsConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SmsConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method SmsConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method SmsConfig[]    findAll()
 * @method SmsConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SmsConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SmsConfig::class);
    }

    // /**
    //  * @return SmsConfig[] Returns an array of SmsConfig objects
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
    public function findOneBySomeField($value): ?SmsConfig
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
