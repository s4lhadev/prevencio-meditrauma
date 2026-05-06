<?php

namespace App\Repository;

use App\Entity\Restriccion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Restriccion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Restriccion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Restriccion[]    findAll()
 * @method Restriccion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestriccionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Restriccion::class);
    }

    // /**
    //  * @return Restriccion[] Returns an array of Restriccion objects
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
    public function findOneBySomeField($value): ?Restriccion
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
