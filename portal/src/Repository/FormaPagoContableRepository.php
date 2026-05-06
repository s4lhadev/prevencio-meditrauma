<?php

namespace App\Repository;

use App\Entity\FormaPagoContable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FormaPagoContable|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormaPagoContable|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormaPagoContable[]    findAll()
 * @method FormaPagoContable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormaPagoContableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormaPagoContable::class);
    }

    // /**
    //  * @return FormaPagoContable[] Returns an array of FormaPagoContable objects
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
    public function findOneBySomeField($value): ?FormaPagoContable
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
