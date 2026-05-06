<?php

namespace App\Repository;

use App\Entity\AnaliticasLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AnaliticasLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method AnaliticasLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method AnaliticasLog[]    findAll()
 * @method AnaliticasLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnaliticasLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnaliticasLog::class);
    }

    // /**
    //  * @return AnaliticasLog[] Returns an array of AnaliticasLog objects
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
    public function findOneBySomeField($value): ?AnaliticasLog
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
