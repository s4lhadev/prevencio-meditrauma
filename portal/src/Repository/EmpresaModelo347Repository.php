<?php

namespace App\Repository;

use App\Entity\EmpresaModelo347;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmpresaModelo347|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmpresaModelo347|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmpresaModelo347[]    findAll()
 * @method EmpresaModelo347[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpresaModelo347Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmpresaModelo347::class);
    }

    // /**
    //  * @return EmpresaModelo347[] Returns an array of EmpresaModelo347 objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EmpresaModelo347
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
