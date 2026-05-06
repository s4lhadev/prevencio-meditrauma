<?php

namespace App\Repository;

use App\Entity\Severidad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Severidad|null find($id, $lockMode = null, $lockVersion = null)
 * @method Severidad|null findOneBy(array $criteria, array $orderBy = null)
 * @method Severidad[]    findAll()
 * @method Severidad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeveridadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Severidad::class);
    }

    // /**
    //  * @return Severidad[] Returns an array of Severidad objects
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
    public function findOneBySomeField($value): ?Severidad
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
