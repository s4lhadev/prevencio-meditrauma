<?php

namespace App\Repository;

use App\Entity\GdocPlantillas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GdocPlantillas|null find($id, $lockMode = null, $lockVersion = null)
 * @method GdocPlantillas|null findOneBy(array $criteria, array $orderBy = null)
 * @method GdocPlantillas[]    findAll()
 * @method GdocPlantillas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GdocPlantillasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GdocPlantillas::class);
    }

    // /**
    //  * @return GdocPlantillas[] Returns an array of GdocPlantillas objects
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
    public function findOneBySomeField($value): ?GdocPlantillas
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
