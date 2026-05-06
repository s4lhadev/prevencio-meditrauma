<?php

namespace App\Repository;

use App\Entity\GdocFichero;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GdocFichero|null find($id, $lockMode = null, $lockVersion = null)
 * @method GdocFichero|null findOneBy(array $criteria, array $orderBy = null)
 * @method GdocFichero[]    findAll()
 * @method GdocFichero[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GdocFicheroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GdocFichero::class);
    }

    // /**
    //  * @return GdocFichero3[] Returns an array of GdocFichero3 objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GdocFichero3
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
