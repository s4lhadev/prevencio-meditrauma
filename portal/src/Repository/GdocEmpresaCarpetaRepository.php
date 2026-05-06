<?php

namespace App\Repository;

use App\Entity\GdocEmpresaCarpeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GdocEmpresaCarpeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method GdocEmpresaCarpeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method GdocEmpresaCarpeta[]    findAll()
 * @method GdocEmpresaCarpeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GdocEmpresaCarpetaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GdocEmpresaCarpeta::class);
    }

    // /**
    //  * @return GdocEmpresaCarpeta[] Returns an array of GdocEmpresaCarpeta objects
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
    public function findOneBySomeField($value): ?GdocTrabajadorCarpeta
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
