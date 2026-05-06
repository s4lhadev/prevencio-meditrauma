<?php

namespace App\Repository;

use App\Entity\Indicador;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Indicador|null find($id, $lockMode = null, $lockVersion = null)
 * @method Indicador|null findOneBy(array $criteria, array $orderBy = null)
 * @method Indicador[]    findAll()
 * @method Indicador[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndicadorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Indicador::class);
    }

    // /**
    //  * @return Indicador[] Returns an array of Indicador objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Indicador
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
