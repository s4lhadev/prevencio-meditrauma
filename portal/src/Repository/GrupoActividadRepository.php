<?php

namespace App\Repository;

use App\Entity\GrupoActividad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GrupoActividad|null find($id, $lockMode = null, $lockVersion = null)
 * @method GrupoActividad|null findOneBy(array $criteria, array $orderBy = null)
 * @method GrupoActividad[]    findAll()
 * @method GrupoActividad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GrupoActividadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GrupoActividad::class);
    }

    // /**
    //  * @return GrupoActividad[] Returns an array of GrupoActividad objects
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
    public function findOneBySomeField($value): ?GrupoActividad
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
