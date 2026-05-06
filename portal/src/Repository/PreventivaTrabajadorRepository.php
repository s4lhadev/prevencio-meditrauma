<?php

namespace App\Repository;

use App\Entity\PreventivaTrabajador;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PreventivaTrabajador|null find($id, $lockMode = null, $lockVersion = null)
 * @method PreventivaTrabajador|null findOneBy(array $criteria, array $orderBy = null)
 * @method PreventivaTrabajador[]    findAll()
 * @method PreventivaTrabajador[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PreventivaTrabajadorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreventivaTrabajador::class);
    }

    // /**
    //  * @return PreventivaTrabajador[] Returns an array of PreventivaTrabajador objects
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
    public function findOneBySomeField($value): ?PreventivaTrabajador
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
