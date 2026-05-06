<?php

namespace App\Repository;

use App\Entity\ListaServiciosContratados;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ListaServiciosContratados|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListaServiciosContratados|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListaServiciosContratados[]    findAll()
 * @method ListaServiciosContratados[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListaServiciosContratadosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListaServiciosContratados::class);
    }

    // /**
    //  * @return ListaServiciosContratados[] Returns an array of ListaServiciosContratados objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ListaServiciosContratados
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
