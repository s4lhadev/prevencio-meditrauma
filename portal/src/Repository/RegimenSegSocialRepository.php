<?php

namespace App\Repository;

use App\Entity\RegimenSegSocial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RegimenSegSocial|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegimenSegSocial|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegimenSegSocial[]    findAll()
 * @method RegimenSegSocial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegimenSegSocialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegimenSegSocial::class);
    }

    // /**
    //  * @return RegimenSegSocial[] Returns an array of RegimenSegSocial objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RegimenSegSocial
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
