<?php

namespace App\Repository;

use App\Entity\GradoCorreccion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GradoCorreccion|null find($id, $lockMode = null, $lockVersion = null)
 * @method GradoCorreccion|null findOneBy(array $criteria, array $orderBy = null)
 * @method GradoCorreccion[]    findAll()
 * @method GradoCorreccion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GradoCorreccionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GradoCorreccion::class);
    }

    // /**
    //  * @return GradoCorreccion[] Returns an array of GradoCorreccion objects
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
    public function findOneBySomeField($value): ?GradoCorreccion
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
