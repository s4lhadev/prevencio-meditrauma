<?php

namespace App\Repository;

use App\Entity\EstadoRevision;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EstadoRevision|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstadoRevision|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstadoRevision[]    findAll()
 * @method EstadoRevision[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstadoRevisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstadoRevision::class);
    }

    // /**
    //  * @return EstadoRevision[] Returns an array of EstadoRevision objects
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
    public function findOneBySomeField($value): ?EstadoRevision
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
