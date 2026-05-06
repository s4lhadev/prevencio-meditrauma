<?php

namespace App\Repository;

use App\Entity\Mutua;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Mutua|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mutua|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mutua[]    findAll()
 * @method Mutua[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MutuaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mutua::class);
    }

    // /**
    //  * @return Mutua[] Returns an array of Mutua objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Mutua
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
