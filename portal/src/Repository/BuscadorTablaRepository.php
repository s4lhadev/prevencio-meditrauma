<?php

namespace App\Repository;

use App\Entity\BuscadorTabla;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BuscadorTabla|null find($id, $lockMode = null, $lockVersion = null)
 * @method BuscadorTabla|null findOneBy(array $criteria, array $orderBy = null)
 * @method BuscadorTabla[]    findAll()
 * @method BuscadorTabla[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BuscadorTablaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BuscadorTabla::class);
    }

    // /**
    //  * @return BuscadorTabla[] Returns an array of BuscadorTabla objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BuscadorTabla
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
