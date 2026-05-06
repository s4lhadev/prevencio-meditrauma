<?php

namespace App\Repository;

use App\Entity\MetodologiaEvaluacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MetodologiaEvaluacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method MetodologiaEvaluacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method MetodologiaEvaluacion[]    findAll()
 * @method MetodologiaEvaluacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MetodologiaEvaluacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MetodologiaEvaluacion::class);
    }

    // /**
    //  * @return MetodologiaEvaluacion[] Returns an array of MetodologiaEvaluacion objects
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
    public function findOneBySomeField($value): ?MetodologiaEvaluacion
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
