<?php

namespace App\Repository;

use App\Entity\PuestoTrabajoMaquinaGenerica;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PuestoTrabajoMaquinaGenerica|null find($id, $lockMode = null, $lockVersion = null)
 * @method PuestoTrabajoMaquinaGenerica|null findOneBy(array $criteria, array $orderBy = null)
 * @method PuestoTrabajoMaquinaGenerica[]    findAll()
 * @method PuestoTrabajoMaquinaGenerica[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PuestoTrabajoMaquinaGenericaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PuestoTrabajoMaquinaGenerica::class);
    }

    // /**
    //  * @return PuestoTrabajoMaquinaGenerica[] Returns an array of PuestoTrabajoMaquinaGenerica objects
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
    public function findOneBySomeField($value): ?PuestoTrabajoMaquinaGenerica
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
