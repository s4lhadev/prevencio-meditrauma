<?php

namespace App\Repository;

use App\Entity\GdocTrabajadorCarpeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GdocTrabajadorCarpeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method GdocTrabajadorCarpeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method GdocTrabajadorCarpeta[]    findAll()
 * @method GdocTrabajadorCarpeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GdocTrabajadorCarpetaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GdocTrabajadorCarpeta::class);
    }

    // /**
    //  * @return GdocTrabajadorCarpeta[] Returns an array of GdocTrabajadorCarpeta objects
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
    public function findOneBySomeField($value): ?GdocTrabajadorCarpeta
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
