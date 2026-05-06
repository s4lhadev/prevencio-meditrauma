<?php

namespace App\Repository;

use App\Entity\Remesa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Remesa|null find($id, $lockMode = null, $lockVersion = null)
 * @method Remesa|null findOneBy(array $criteria, array $orderBy = null)
 * @method Remesa[]    findAll()
 * @method Remesa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RemesaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Remesa::class);
    }

    // /**
    //  * @return Remesa[] Returns an array of Remesa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Remesa
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
