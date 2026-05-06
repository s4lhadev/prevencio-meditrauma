<?php

namespace App\Repository;

use App\Entity\PuestoTrabajoEvaluacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PuestoTrabajoEvaluacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method PuestoTrabajoEvaluacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method PuestoTrabajoEvaluacion[]    findAll()
 * @method PuestoTrabajoEvaluacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PuestoTrabajoEvaluacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PuestoTrabajoEvaluacion::class);
    }

    // /**
    //  * @return PuestoTrabajoEvaluacion[] Returns an array of PuestoTrabajoEvaluacion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PuestoTrabajoEvaluacion
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
