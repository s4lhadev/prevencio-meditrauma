<?php

namespace App\Repository;

use App\Entity\ActividadPreventiva;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ActividadPreventiva|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActividadPreventiva|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActividadPreventiva[]    findAll()
 * @method ActividadPreventiva[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActividadPreventivaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActividadPreventiva::class);
    }

    // /**
    //  * @return ActividadPreventiva[] Returns an array of ActividadPreventiva objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ActividadPreventiva
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
