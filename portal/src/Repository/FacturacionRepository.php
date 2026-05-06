<?php

namespace App\Repository;

use App\Entity\Facturacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Facturacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Facturacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Facturacion[]    findAll()
 * @method Facturacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FacturacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facturacion::class);
    }

    // /**
    //  * @return Facturacion[] Returns an array of Facturacion objects
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
    public function findOneBySomeField($value): ?Facturacion
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
