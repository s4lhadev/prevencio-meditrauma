<?php

namespace App\Repository;

use App\Entity\SectorEmpresarial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SectorEmpresarial|null find($id, $lockMode = null, $lockVersion = null)
 * @method SectorEmpresarial|null findOneBy(array $criteria, array $orderBy = null)
 * @method SectorEmpresarial[]    findAll()
 * @method SectorEmpresarial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SectorEmpresarialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SectorEmpresarial::class);
    }

    // /**
    //  * @return SectorEmpresarial[] Returns an array of SectorEmpresarial objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SectorEmpresarial
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
