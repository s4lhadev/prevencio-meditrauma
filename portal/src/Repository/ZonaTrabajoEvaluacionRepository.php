<?php

namespace App\Repository;

use App\Entity\ZonaTrabajoEvaluacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZonaTrabajoEvaluacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZonaTrabajoEvaluacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZonaTrabajoEvaluacion[]    findAll()
 * @method ZonaTrabajoEvaluacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZonaTrabajoEvaluacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ZonaTrabajoEvaluacion::class);
    }

    // /**
    //  * @return ZonaTrabajoEvaluacion[] Returns an array of ZonaTrabajoEvaluacion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('z.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ZonaTrabajoEvaluacion
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
