<?php

namespace App\Repository;

use App\Entity\EmpresaPlanPrevencion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmpresaPlanPrevencion|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmpresaPlanPrevencion|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmpresaPlanPrevencion[]    findAll()
 * @method EmpresaPlanPrevencion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpresaPlanPrevencionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmpresaPlanPrevencion::class);
    }

    // /**
    //  * @return EmpresaPlanPrevencion[] Returns an array of EmpresaPlanPrevencion objects
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
    public function findOneBySomeField($value): ?EmpresaPlanPrevencion
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
