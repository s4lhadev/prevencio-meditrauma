<?php

namespace App\Repository;

use App\Entity\CentroTrabajoEmpresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CentroTrabajoEmpresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method CentroTrabajoEmpresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method CentroTrabajoEmpresa[]    findAll()
 * @method CentroTrabajoEmpresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CentroTrabajoEmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CentroTrabajoEmpresa::class);
    }

    // /**
    //  * @return CentroTrabajoEmpresa[] Returns an array of CentroTrabajoEmpresa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CentroTrabajoEmpresa
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
