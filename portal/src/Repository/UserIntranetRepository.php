<?php

namespace App\Repository;

use App\Entity\UserIntranet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserIntranet|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserIntranet|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserIntranet[]    findAll()
 * @method UserIntranet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserIntranetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserIntranet::class);
    }

    // /**
    //  * @return UserIntranet[] Returns an array of UserIntranet objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserIntranet
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
