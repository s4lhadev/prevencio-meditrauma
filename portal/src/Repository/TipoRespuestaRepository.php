<?php

namespace App\Repository;

use App\Entity\TipoRespuesta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TipoRespuesta|null find($id, $lockMode = null, $lockVersion = null)
 * @method TipoRespuesta|null findOneBy(array $criteria, array $orderBy = null)
 * @method TipoRespuesta[]    findAll()
 * @method TipoRespuesta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TipoRespuestaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TipoRespuesta::class);
    }

    // /**
    //  * @return TipoRespuesta[] Returns an array of TipoRespuesta objects
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
    public function findOneBySomeField($value): ?TipoRespuesta
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
