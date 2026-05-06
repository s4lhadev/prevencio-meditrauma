<?php

namespace App\Repository;

use App\Entity\PuestoTrabajoTrabajador;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PuestoTrabajoTrabajador|null find($id, $lockMode = null, $lockVersion = null)
 * @method PuestoTrabajoTrabajador|null findOneBy(array $criteria, array $orderBy = null)
 * @method PuestoTrabajoTrabajador[]    findAll()
 * @method PuestoTrabajoTrabajador[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PuestoTrabajoTrabajadorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PuestoTrabajoTrabajador::class);
    }

    // /**
    //  * @return PuestoTrabajoTrabajador[] Returns an array of PuestoTrabajoTrabajador objects
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
    public function findOneBySomeField($value): ?PuestoTrabajoTrabajador
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
