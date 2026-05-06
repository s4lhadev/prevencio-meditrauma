<?php

namespace App\Repository;

use App\Entity\RevisionDocumentoAdjunto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RevisionDocumentoAdjunto|null find($id, $lockMode = null, $lockVersion = null)
 * @method RevisionDocumentoAdjunto|null findOneBy(array $criteria, array $orderBy = null)
 * @method RevisionDocumentoAdjunto[]    findAll()
 * @method RevisionDocumentoAdjunto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RevisionDocumentoAdjuntoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RevisionDocumentoAdjunto::class);
    }

    // /**
    //  * @return RevisionDocumentoAdjunto[] Returns an array of RevisionDocumentoAdjunto objects
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
    public function findOneBySomeField($value): ?RevisionDocumentoAdjunto
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
