<?php

namespace App\Repository;

use App\Entity\PlanificacionRiesgoCausa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlanificacionRiesgoCausa|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlanificacionRiesgoCausa|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlanificacionRiesgoCausa[]    findAll()
 * @method PlanificacionRiesgoCausa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlanificacionRiesgoCausaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlanificacionRiesgoCausa::class);
    }

    // /**
    //  * @return PlanificacionRiesgoCausa[] Returns an array of PlanificacionRiesgoCausa objects
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
    public function findOneBySomeField($value): ?PlanificacionRiesgoCausa
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
