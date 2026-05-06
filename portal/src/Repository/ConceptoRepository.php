<?php

namespace App\Repository;

use App\Entity\Concepto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Concepto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Concepto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Concepto[]    findAll()
 * @method Concepto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConceptoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Concepto::class);
    }

    // /**
    //  * @return Concepto[] Returns an array of Concepto objects
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
    public function findOneBySomeField($value): ?Concepto
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
