<?php

namespace App\Repository;

use App\Entity\GrupoEmpresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GrupoEmpresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method GrupoEmpresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method GrupoEmpresa[]    findAll()
 * @method GrupoEmpresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GrupoEmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GrupoEmpresa::class);
    }

    // /**
    //  * @return GrupoEmpresa[] Returns an array of GrupoEmpresa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GrupoEmpresa
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
