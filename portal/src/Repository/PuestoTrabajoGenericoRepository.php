<?php

namespace App\Repository;

use App\Entity\PuestoTrabajoGenerico;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PuestoTrabajoGenerico|null find($id, $lockMode = null, $lockVersion = null)
 * @method PuestoTrabajoGenerico|null findOneBy(array $criteria, array $orderBy = null)
 * @method PuestoTrabajoGenerico[]    findAll()
 * @method PuestoTrabajoGenerico[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PuestoTrabajoGenericoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PuestoTrabajoGenerico::class);
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
