<?php

namespace App\Repository;

use App\Entity\RiesgoCausaEvaluacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RiesgoCausaEvaluacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method RiesgoCausaEvaluacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method RiesgoCausaEvaluacion[]    findAll()
 * @method RiesgoCausaEvaluacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RiesgoCausaEvaluacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RiesgoCausaEvaluacion::class);
    }

    // /**
    //  * @return RiesgoCausaEvaluacion[] Returns an array of RiesgoCausaEvaluacion objects
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
    public function findOneBySomeField($value): ?RiesgoCausaEvaluacion
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
