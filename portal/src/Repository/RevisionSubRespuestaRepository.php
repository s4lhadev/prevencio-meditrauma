<?php

namespace App\Repository;

use App\Entity\RevisionSubRespuesta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RevisionSubRespuesta|null find($id, $lockMode = null, $lockVersion = null)
 * @method RevisionSubRespuesta|null findOneBy(array $criteria, array $orderBy = null)
 * @method RevisionSubRespuesta[]    findAll()
 * @method RevisionSubRespuesta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RevisionSubRespuestaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RevisionSubRespuesta::class);
    }

    // /**
    //  * @return RevisionSubRespuesta[] Returns an array of RevisionSubRespuesta objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RevisionSubRespuesta
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
