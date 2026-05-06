<?php

namespace App\Repository;

use App\Entity\BuscadorVariable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BuscadorVariable|null find($id, $lockMode = null, $lockVersion = null)
 * @method BuscadorVariable|null findOneBy(array $criteria, array $orderBy = null)
 * @method BuscadorVariable[]    findAll()
 * @method BuscadorVariable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BuscadorVariableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BuscadorVariable::class);
    }

    // /**
    //  * @return BuscadorVariable[] Returns an array of BuscadorVariable objects
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
    public function findOneBySomeField($value): ?BuscadorVariable
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
