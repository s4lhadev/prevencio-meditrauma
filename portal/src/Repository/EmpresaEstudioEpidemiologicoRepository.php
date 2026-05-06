<?php

namespace App\Repository;

use App\Entity\EmpresaEstudioEpidemiologico;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmpresaEstudioEpidemiologico|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmpresaEstudioEpidemiologico|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmpresaEstudioEpidemiologico[]    findAll()
 * @method EmpresaEstudioEpidemiologico[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpresaEstudioEpidemiologicoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmpresaEstudioEpidemiologico::class);
    }

    // /**
    //  * @return EmpresaEstudioEpidemiologico[] Returns an array of EmpresaEstudioEpidemiologico objects
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
    public function findOneBySomeField($value): ?EmpresaEstudioEpidemiologico
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
