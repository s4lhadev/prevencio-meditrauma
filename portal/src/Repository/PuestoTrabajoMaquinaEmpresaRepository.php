<?php

namespace App\Repository;

use App\Entity\PuestoTrabajoMaquinaEmpresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PuestoTrabajoMaquinaEmpresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method PuestoTrabajoMaquinaEmpresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method PuestoTrabajoMaquinaEmpresa[]    findAll()
 * @method PuestoTrabajoMaquinaEmpresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PuestoTrabajoMaquinaEmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PuestoTrabajoMaquinaEmpresa::class);
    }

    // /**
    //  * @return PuestoTrabajoMaquinaEmpresa[] Returns an array of PuestoTrabajoMaquinaEmpresa objects
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
    public function findOneBySomeField($value): ?PuestoTrabajoMaquinaEmpresa
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
