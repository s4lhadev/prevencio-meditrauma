<?php

namespace App\Repository;

use App\Entity\Epi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Epi|null find($id, $lockMode = null, $lockVersion = null)
 * @method Epi|null findOneBy(array $criteria, array $orderBy = null)
 * @method Epi[]    findAll()
 * @method Epi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Epi::class);
    }

    // /**
    //  * @return Epi[] Returns an array of Epi objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Epi
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
