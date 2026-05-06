<?php

namespace App\Repository;

use App\Entity\GdocTrabajador;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GdocTrabajador|null find($id, $lockMode = null, $lockVersion = null)
 * @method GdocTrabajador|null findOneBy(array $criteria, array $orderBy = null)
 * @method GdocTrabajador[]    findAll()
 * @method GdocTrabajador[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GdocTrabajadorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GdocTrabajador::class);
    }

    // /**
    //  * @return GdocTrabajador[] Returns an array of GdocTrabajador objects
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
