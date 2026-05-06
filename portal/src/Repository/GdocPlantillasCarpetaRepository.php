<?php

namespace App\Repository;

use App\Entity\GdocPlantillasCarpeta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GdocPlantillasCarpeta|null find($id, $lockMode = null, $lockVersion = null)
 * @method GdocPlantillasCarpeta|null findOneBy(array $criteria, array $orderBy = null)
 * @method GdocPlantillasCarpeta[]    findAll()
 * @method GdocPlantillasCarpeta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GdocPlantillasCarpetaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GdocPlantillasCarpeta::class);
    }

    // /**
    //  * @return GdocPlantillasCarpeta[] Returns an array of GdocPlantillasCarpeta objects
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
    public function findOneBySomeField($value): ?GdocPlantillasCarpeta
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
