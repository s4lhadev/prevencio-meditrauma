<?php

namespace App\Repository;

use App\Entity\GrupoContaminante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GrupoContaminante|null find($id, $lockMode = null, $lockVersion = null)
 * @method GrupoContaminante|null findOneBy(array $criteria, array $orderBy = null)
 * @method GrupoContaminante[]    findAll()
 * @method GrupoContaminante[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GrupoContaminanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GrupoContaminante::class);
    }

    // /**
    //  * @return GrupoContaminante[] Returns an array of GrupoContaminante objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GrupoContaminante
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
