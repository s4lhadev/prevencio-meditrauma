<?php

namespace App\Repository;

use App\Entity\GrupoRiesgo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GrupoRiesgo|null find($id, $lockMode = null, $lockVersion = null)
 * @method GrupoRiesgo|null findOneBy(array $criteria, array $orderBy = null)
 * @method GrupoRiesgo[]    findAll()
 * @method GrupoRiesgo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GrupoRiesgoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GrupoRiesgo::class);
    }

    // /**
    //  * @return GrupoRiesgo[] Returns an array of GrupoRiesgo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GrupoRiesgo
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
