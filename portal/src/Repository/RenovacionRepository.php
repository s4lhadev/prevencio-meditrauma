<?php

namespace App\Repository;

use App\Entity\Renovacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Renovacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Renovacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Renovacion[]    findAll()
 * @method Renovacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RenovacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Renovacion::class);
    }

    // /**
    //  * @return Renovacion[] Returns an array of Renovacion objects
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
    public function findOneBySomeField($value): ?Renovacion
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
