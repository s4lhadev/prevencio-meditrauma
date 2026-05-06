<?php

namespace App\Repository;

use App\Entity\GiroBancario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GiroBancario|null find($id, $lockMode = null, $lockVersion = null)
 * @method GiroBancario|null findOneBy(array $criteria, array $orderBy = null)
 * @method GiroBancario[]    findAll()
 * @method GiroBancario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GiroBancarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GiroBancario::class);
    }

    // /**
    //  * @return GiroBancario[] Returns an array of GiroBancario objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GiroBancario
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
