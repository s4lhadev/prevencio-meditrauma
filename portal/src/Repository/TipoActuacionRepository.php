<?php

namespace App\Repository;

use App\Entity\TipoActuacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TipoActuacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method TipoActuacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method TipoActuacion[]    findAll()
 * @method TipoActuacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TipoActuacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TipoActuacion::class);
    }

    // /**
    //  * @return TipoActuacion[] Returns an array of TipoActuacion objects
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
    public function findOneBySomeField($value): ?TipoActuacion
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
