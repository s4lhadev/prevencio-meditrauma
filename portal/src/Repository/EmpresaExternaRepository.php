<?php

namespace App\Repository;

use App\Entity\EmpresaExterna;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmpresaExterna|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmpresaExterna|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmpresaExterna[]    findAll()
 * @method EmpresaExterna[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpresaExternaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmpresaExterna::class);
    }

    // /**
    //  * @return EmpresaExterna[] Returns an array of EmpresaExterna objects
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
    public function findOneBySomeField($value): ?EmpresaExterna
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
