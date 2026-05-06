<?php

namespace App\Repository;

use App\Entity\CnaeEmpresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnaeEmpresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnaeEmpresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnaeEmpresa[]    findAll()
 * @method CnaeEmpresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnaeEmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnaeEmpresa::class);
    }

    // /**
    //  * @return CnaeEmpresa[] Returns an array of CnaeEmpresa objects
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
    public function findOneBySomeField($value): ?CnaeEmpresa
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
