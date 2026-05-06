<?php

namespace App\Repository;

use App\Entity\GrupoPreventiva;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GrupoPreventiva|null find($id, $lockMode = null, $lockVersion = null)
 * @method GrupoPreventiva|null findOneBy(array $criteria, array $orderBy = null)
 * @method GrupoPreventiva[]    findAll()
 * @method GrupoPreventiva[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GrupoPreventivaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GrupoPreventiva::class);
    }

    // /**
    //  * @return GrupoPreventiva[] Returns an array of GrupoPreventiva objects
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
    public function findOneBySomeField($value): ?GrupoPreventiva
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
