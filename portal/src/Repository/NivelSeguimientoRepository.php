<?php

namespace App\Repository;

use App\Entity\NivelSeguimiento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NivelSeguimiento|null find($id, $lockMode = null, $lockVersion = null)
 * @method NivelSeguimiento|null findOneBy(array $criteria, array $orderBy = null)
 * @method NivelSeguimiento[]    findAll()
 * @method NivelSeguimiento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NivelSeguimientoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NivelSeguimiento::class);
    }

    // /**
    //  * @return NivelSeguimiento[] Returns an array of NivelSeguimiento objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NivelSeguimiento
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
