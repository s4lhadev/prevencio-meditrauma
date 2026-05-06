<?php

namespace App\Repository;

use App\Entity\LogEnvioMail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LogEnvioMail|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogEnvioMail|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogEnvioMail[]    findAll()
 * @method LogEnvioMail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogEnvioMailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogEnvioMail::class);
    }

    // /**
    //  * @return LogEnvioMail[] Returns an array of LogEnvioMail objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LogEnvioMail
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
