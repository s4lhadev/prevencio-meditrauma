<?php

namespace App\Repository;

use App\Entity\PuestoTrabajoCentro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PuestoTrabajoCentro|null find($id, $lockMode = null, $lockVersion = null)
 * @method PuestoTrabajoCentro|null findOneBy(array $criteria, array $orderBy = null)
 * @method PuestoTrabajoCentro[]    findAll()
 * @method PuestoTrabajoCentro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PuestoTrabajoCentroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PuestoTrabajoCentro::class);
    }

    // /**
    //  * @return PuestoTrabajoCentro[] Returns an array of PuestoTrabajoCentro objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PuestoTrabajoCentro
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
