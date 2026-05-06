<?php

namespace App\Repository;

use App\Entity\GrupoMaquina;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GrupoMaquina|null find($id, $lockMode = null, $lockVersion = null)
 * @method GrupoMaquina|null findOneBy(array $criteria, array $orderBy = null)
 * @method GrupoMaquina[]    findAll()
 * @method GrupoMaquina[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GrupoMaquinaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GrupoMaquina::class);
    }

    // /**
    //  * @return GrupoMaquina[] Returns an array of GrupoMaquina objects
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
    public function findOneBySomeField($value): ?GrupoMaquina
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
