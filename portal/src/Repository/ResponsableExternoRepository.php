<?php

namespace App\Repository;

use App\Entity\ResponsableExterno;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ResponsableExterno|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResponsableExterno|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResponsableExterno[]    findAll()
 * @method ResponsableExterno[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResponsableExternoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResponsableExterno::class);
    }

    // /**
    //  * @return ResponsableExterno[] Returns an array of ResponsableExterno objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ResponsableExterno
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
