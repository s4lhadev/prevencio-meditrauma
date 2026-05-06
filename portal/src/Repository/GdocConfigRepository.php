<?php

namespace App\Repository;

use App\Entity\GdocConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GdocConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method GdocConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method GdocConfig[]    findAll()
 * @method GdocConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GdocConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GdocConfig::class);
    }

    // /**
    //  * @return ConfigGdoc[] Returns an array of ConfigGdoc objects
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
    public function findOneBySomeField($value): ?ConfigGdoc
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
