<?php

namespace App\Repository;

use App\Entity\SerieRespuesta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SerieRespuesta|null find($id, $lockMode = null, $lockVersion = null)
 * @method SerieRespuesta|null findOneBy(array $criteria, array $orderBy = null)
 * @method SerieRespuesta[]    findAll()
 * @method SerieRespuesta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SerieRespuestaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SerieRespuesta::class);
    }

    // /**
    //  * @return SeriesRespuesta[] Returns an array of SeriesRespuesta objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SeriesRespuesta
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
