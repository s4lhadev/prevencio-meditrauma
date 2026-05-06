<?php

namespace App\Repository;

use App\Entity\RevisionRespuesta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RevisionRespuesta|null find($id, $lockMode = null, $lockVersion = null)
 * @method RevisionRespuesta|null findOneBy(array $criteria, array $orderBy = null)
 * @method RevisionRespuesta[]    findAll()
 * @method RevisionRespuesta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RevisionRespuestaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RevisionRespuesta::class);
    }

    // /**
    //  * @return RevisionRespuesta[] Returns an array of RevisionRespuesta objects
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
    public function findOneBySomeField($value): ?RevisionRespuesta
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
