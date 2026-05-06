<?php

namespace App\Repository;

use App\Entity\CuestionarioPregunta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CuestionarioPregunta|null find($id, $lockMode = null, $lockVersion = null)
 * @method CuestionarioPregunta|null findOneBy(array $criteria, array $orderBy = null)
 * @method CuestionarioPregunta[]    findAll()
 * @method CuestionarioPregunta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CuestionarioPreguntaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CuestionarioPregunta::class);
    }

    // /**
    //  * @return CuestionarioPregunta[] Returns an array of CuestionarioPregunta objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CuestionarioPregunta
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
