<?php

namespace App\Repository;

use App\Entity\ContratoModalidad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContratoModalidad|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoModalidad|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContratoModalidad[]    findAll()
 * @method ContratoModalidad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoModalidadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoModalidad::class);
    }

    // /**
    //  * @return ContratoModalidad[] Returns an array of ContratoModalidad objects
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
    public function findOneBySomeField($value): ?ContratoModalidad
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
