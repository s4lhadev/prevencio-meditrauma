<?php

namespace App\Repository;

use App\Entity\AlteracionAnaliticaRevision;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AlteracionAnaliticaRevision|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlteracionAnaliticaRevision|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlteracionAnaliticaRevision[]    findAll()
 * @method AlteracionAnaliticaRevision[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlteracionAnaliticaRevisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlteracionAnaliticaRevision::class);
    }

    // /**
    //  * @return AlteracionAnaliticaRevision[] Returns an array of AlteracionAnaliticaRevision objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AlteracionAnaliticaRevision
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
