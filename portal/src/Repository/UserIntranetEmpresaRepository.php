<?php

namespace App\Repository;

use App\Entity\UserIntranetEmpresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserIntranetEmpresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserIntranetEmpresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserIntranetEmpresa[]    findAll()
 * @method UserIntranetEmpresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserIntranetEmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserIntranetEmpresa::class);
    }

    // /**
    //  * @return UserIntranetEmpresa[] Returns an array of UserIntranetEmpresa objects
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
    public function findOneBySomeField($value): ?UserIntranetEmpresa
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
