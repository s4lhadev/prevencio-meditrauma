<?php

namespace App\Repository;

use App\Entity\SubPreguntaPregunta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SubPreguntaPregunta|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubPreguntaPregunta|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubPreguntaPregunta[]    findAll()
 * @method SubPreguntaPregunta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubPreguntaPreguntaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubPreguntaPregunta::class);
    }

    // /**
    //  * @return SubPreguntaPregunta[] Returns an array of SubPreguntaPregunta objects
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
    public function findOneBySomeField($value): ?SubPreguntaPregunta
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
