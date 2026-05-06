<?php

namespace App\Repository;

use App\Entity\BuscadorQueriesVariable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BuscadorQueriesVariable|null find($id, $lockMode = null, $lockVersion = null)
 * @method BuscadorQueriesVariable|null findOneBy(array $criteria, array $orderBy = null)
 * @method BuscadorQueriesVariable[]    findAll()
 * @method BuscadorQueriesVariable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BuscadorQueriesVariableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BuscadorQueriesVariable::class);
    }

    // /**
    //  * @return BuscadorQueriesVariable[] Returns an array of BuscadorQueriesVariable objects
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
    public function findOneBySomeField($value): ?BuscadorQueriesVariable
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
