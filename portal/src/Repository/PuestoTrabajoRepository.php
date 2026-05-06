<?php

namespace App\Repository;

use App\Entity\PuestoTrabajo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PuestoTrabajo|null find($id, $lockMode = null, $lockVersion = null)
 * @method PuestoTrabajo|null findOneBy(array $criteria, array $orderBy = null)
 * @method PuestoTrabajo[]    findAll()
 * @method PuestoTrabajo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PuestoTrabajoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PuestoTrabajo::class);
    }

    // /**
    //  * @return PuestoTrabajo[] Returns an array of PuestoTrabajo objects
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
    public function findOneBySomeField($value): ?PuestoTrabajo
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
