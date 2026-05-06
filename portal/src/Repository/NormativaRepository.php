<?php

namespace App\Repository;

use App\Entity\Normativa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Normativa|null find($id, $lockMode = null, $lockVersion = null)
 * @method Normativa|null findOneBy(array $criteria, array $orderBy = null)
 * @method Normativa[]    findAll()
 * @method Normativa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NormativaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Normativa::class);
    }

    // /**
    //  * @return Normativa[] Returns an array of Normativa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Normativa
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
