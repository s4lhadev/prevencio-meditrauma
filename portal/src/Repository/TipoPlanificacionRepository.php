<?php

namespace App\Repository;

use App\Entity\TipoPlanificacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TipoPlanificacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method TipoPlanificacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method TipoPlanificacion[]    findAll()
 * @method TipoPlanificacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TipoPlanificacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TipoPlanificacion::class);
    }

    // /**
    //  * @return TipoPlanificacion[] Returns an array of TipoPlanificacion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TipoPlanificacion
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
