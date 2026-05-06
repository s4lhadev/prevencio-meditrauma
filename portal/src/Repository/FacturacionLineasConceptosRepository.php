<?php

namespace App\Repository;

use App\Entity\FacturacionLineasConceptos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FacturacionLineasConceptos|null find($id, $lockMode = null, $lockVersion = null)
 * @method FacturacionLineasConceptos|null findOneBy(array $criteria, array $orderBy = null)
 * @method FacturacionLineasConceptos[]    findAll()
 * @method FacturacionLineasConceptos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FacturacionLineasConceptosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FacturacionLineasConceptos::class);
    }

    // /**
    //  * @return FacturacionLineas[] Returns an array of FacturacionLineas objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FacturacionLineas
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
