<?php

namespace App\Repository;

use App\Entity\TrabajadorAltaBaja;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TrabajadorAltaBaja|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrabajadorAltaBaja|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrabajadorAltaBaja[]    findAll()
 * @method TrabajadorAltaBaja[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrabajadorAltaBajaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrabajadorAltaBaja::class);
    }

    // /**
    //  * @return TrabajadorAltaBaja[] Returns an array of TrabajadorAltaBaja objects
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
    public function findOneBySomeField($value): ?TrabajadorAltaBaja
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
