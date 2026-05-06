<?php

namespace App\Repository;

use App\Entity\CodigoEmpresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CodigoEmpresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodigoEmpresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodigoEmpresa[]    findAll()
 * @method CodigoEmpresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodigoEmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodigoEmpresa::class);
    }

    // /**
    //  * @return CodigoEmpresa[] Returns an array of CodigoEmpresa objects
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
    public function findOneBySomeField($value): ?CodigoEmpresa
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
