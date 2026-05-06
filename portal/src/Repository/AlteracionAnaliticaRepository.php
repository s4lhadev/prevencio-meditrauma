<?php

namespace App\Repository;

use App\Entity\AlteracionAnalitica;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AlteracionAnalitica|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlteracionAnalitica|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlteracionAnalitica[]    findAll()
 * @method AlteracionAnalitica[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlteracionAnaliticaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlteracionAnalitica::class);
    }

    // /**
    //  * @return AlteracionAnalitica[] Returns an array of AlteracionAnalitica objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AlteracionAnalitica
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
