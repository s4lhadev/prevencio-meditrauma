<?php

namespace App\Repository;

use App\Entity\FormulaVariable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FormulaVariable|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormulaVariable|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormulaVariable[]    findAll()
 * @method FormulaVariable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormulaVariableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormulaVariable::class);
    }

    // /**
    //  * @return FormulaVariable[] Returns an array of FormulaVariable objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FormulaVariable
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
