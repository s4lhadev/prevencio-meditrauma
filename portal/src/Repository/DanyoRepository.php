<?php

namespace App\Repository;

use App\Entity\Danyo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Danyo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Danyo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Danyo[]    findAll()
 * @method Danyo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DanyoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Danyo::class);
    }

    // /**
    //  * @return Danyo[] Returns an array of Danyo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Danyo
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
