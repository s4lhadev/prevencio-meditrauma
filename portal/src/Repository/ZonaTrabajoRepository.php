<?php

namespace App\Repository;

use App\Entity\ZonaTrabajo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZonaTrabajo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZonaTrabajo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZonaTrabajo[]    findAll()
 * @method ZonaTrabajo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZonaTrabajoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ZonaTrabajo::class);
    }

    // /**
    //  * @return ZonaTrabajo[] Returns an array of ZonaTrabajo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('z.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ZonaTrabajo
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
