<?php

namespace App\Repository;

use App\Entity\PrivilegioRoles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PrivilegioRoles|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrivilegioRoles|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrivilegioRoles[]    findAll()
 * @method PrivilegioRoles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrivilegioRolesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrivilegioRoles::class);
    }

    // /**
    //  * @return PrivilegioRoles[] Returns an array of PrivilegioRoles objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PrivilegioRoles
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
