<?php

namespace App\Repository;

use App\Entity\EstadoPrevencion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EstadoPrevencion|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstadoPrevencion|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstadoPrevencion[]    findAll()
 * @method EstadoPrevencion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstadoPrevencionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstadoPrevencion::class);
    }

    // /**
    //  * @return EstadoPrevencion[] Returns an array of EstadoPrevencion objects
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
    public function findOneBySomeField($value): ?EstadoPrevencion
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
