<?php

namespace App\Repository;

use App\Entity\TipoCentro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TipoCentro|null find($id, $lockMode = null, $lockVersion = null)
 * @method TipoCentro|null findOneBy(array $criteria, array $orderBy = null)
 * @method TipoCentro[]    findAll()
 * @method TipoCentro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TipoCentroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TipoCentro::class);
    }

    // /**
    //  * @return TipoCentro[] Returns an array of TipoCentro objects
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
    public function findOneBySomeField($value): ?TipoCentro
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
