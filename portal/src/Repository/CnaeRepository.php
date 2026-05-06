<?php

namespace App\Repository;

use App\Entity\Cnae;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cnae|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cnae|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cnae[]    findAll()
 * @method Cnae[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnaeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cnae::class);
    }

    // /**
    //  * @return Cnae[] Returns an array of Cnae objects
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
    public function findOneBySomeField($value): ?Cnae
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
