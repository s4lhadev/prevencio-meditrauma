<?php

namespace App\Repository;

use App\Entity\GiroBancarioDevolucion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GiroBancarioDevolucion|null find($id, $lockMode = null, $lockVersion = null)
 * @method GiroBancarioDevolucion|null findOneBy(array $criteria, array $orderBy = null)
 * @method GiroBancarioDevolucion[]    findAll()
 * @method GiroBancarioDevolucion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GiroBancarioDevolucionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GiroBancarioDevolucion::class);
    }

    // /**
    //  * @return GiroBancarioDevolucion[] Returns an array of GiroBancarioDevolucion objects
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
    public function findOneBySomeField($value): ?GiroBancario
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
