<?php

namespace App\Repository;

use App\Entity\EntidadBancaria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EntidadBancaria|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntidadBancaria|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntidadBancaria[]    findAll()
 * @method EntidadBancaria[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntidadBancariaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntidadBancaria::class);
    }

    // /**
    //  * @return EntidadBancaria[] Returns an array of EntidadBancaria objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EntidadBancaria
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
