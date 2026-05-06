<?php

namespace App\Repository;

use App\Entity\Consecuencia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Consecuencia|null find($id, $lockMode = null, $lockVersion = null)
 * @method Consecuencia|null findOneBy(array $criteria, array $orderBy = null)
 * @method Consecuencia[]    findAll()
 * @method Consecuencia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsecuenciaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consecuencia::class);
    }

    // /**
    //  * @return Consecuencia[] Returns an array of Consecuencia objects
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
    public function findOneBySomeField($value): ?Consecuencia
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
