<?php

namespace App\Repository;

use App\Entity\GrupoNormativa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GrupoNormativa|null find($id, $lockMode = null, $lockVersion = null)
 * @method GrupoNormativa|null findOneBy(array $criteria, array $orderBy = null)
 * @method GrupoNormativa[]    findAll()
 * @method GrupoNormativa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GrupoNormativaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GrupoNormativa::class);
    }

    // /**
    //  * @return GrupoNormativa[] Returns an array of GrupoNormativa objects
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
    public function findOneBySomeField($value): ?GrupoNormativa
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
