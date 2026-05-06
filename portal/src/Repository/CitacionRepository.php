<?php

namespace App\Repository;

use App\Entity\Citacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Citacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Citacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Citacion[]    findAll()
 * @method Citacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CitacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Citacion::class);
    }

    // /**
    //  * @return Citacion[] Returns an array of Citacion objects
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
    public function findOneBySomeField($value): ?Citacion
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
