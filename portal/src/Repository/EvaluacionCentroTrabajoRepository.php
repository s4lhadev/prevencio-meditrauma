<?php

namespace App\Repository;

use App\Entity\EvaluacionCentroTrabajo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvaluacionCentroTrabajo|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvaluacionCentroTrabajo|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvaluacionCentroTrabajo[]    findAll()
 * @method EvaluacionCentroTrabajo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvaluacionCentroTrabajoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvaluacionCentroTrabajo::class);
    }

    // /**
    //  * @return EvaluacionCentroTrabajo[] Returns an array of EvaluacionCentroTrabajo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EvaluacionCentroTrabajo
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
