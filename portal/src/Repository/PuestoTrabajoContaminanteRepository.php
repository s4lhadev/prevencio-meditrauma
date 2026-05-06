<?php

namespace App\Repository;

use App\Entity\PuestoTrabajoContaminante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PuestoTrabajoContaminante|null find($id, $lockMode = null, $lockVersion = null)
 * @method PuestoTrabajoContaminante|null findOneBy(array $criteria, array $orderBy = null)
 * @method PuestoTrabajoContaminante[]    findAll()
 * @method PuestoTrabajoContaminante[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PuestoTrabajoContaminanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PuestoTrabajoContaminante::class);
    }

    // /**
    //  * @return PuestoTrabajoContaminante[] Returns an array of PuestoTrabajoContaminante objects
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
    public function findOneBySomeField($value): ?PuestoTrabajoContaminante
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
