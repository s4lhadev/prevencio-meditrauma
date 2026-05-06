<?php

namespace App\Repository;

use App\Entity\AnaliticasConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AnaliticasConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method AnaliticasConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method AnaliticasConfig[]    findAll()
 * @method AnaliticasConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnaliticasConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnaliticasConfig::class);
    }

    // /**
    //  * @return AnaliticasConfig[] Returns an array of AnaliticasConfig objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AnaliticasConfig
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
