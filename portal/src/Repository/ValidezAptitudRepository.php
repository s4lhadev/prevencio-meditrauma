<?php

namespace App\Repository;

use App\Entity\ValidezAptitud;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ValidezAptitud|null find($id, $lockMode = null, $lockVersion = null)
 * @method ValidezAptitud|null findOneBy(array $criteria, array $orderBy = null)
 * @method ValidezAptitud[]    findAll()
 * @method ValidezAptitud[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ValidezAptitudRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ValidezAptitud::class);
    }

    // /**
    //  * @return ValidezAptitud[] Returns an array of ValidezAptitud objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ValidezAptitud
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
