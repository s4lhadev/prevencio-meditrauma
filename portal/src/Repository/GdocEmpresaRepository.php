<?php

namespace App\Repository;

use App\Entity\GdocEmpresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GdocEmpresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method GdocEmpresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method GdocEmpresa[]    findAll()
 * @method GdocEmpresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GdocEmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GdocEmpresa::class);
    }

    // /**
    //  * @return GdocEmpresa[] Returns an array of GdocEmpresa objects
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
    public function findOneBySomeField($value): ?GdocTrabajador
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
