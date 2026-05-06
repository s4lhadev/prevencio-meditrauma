<?php

namespace App\Repository;

use App\Entity\AptitudRestriccion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AptitudRestriccion|null find($id, $lockMode = null, $lockVersion = null)
 * @method AptitudRestriccion|null findOneBy(array $criteria, array $orderBy = null)
 * @method AptitudRestriccion[]    findAll()
 * @method AptitudRestriccion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AptitudRestriccionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AptitudRestriccion::class);
    }

    // /**
    //  * @return AptitudRestriccion[] Returns an array of AptitudRestriccion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AptitudRestriccion
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
