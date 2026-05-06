<?php

namespace App\Repository;

use App\Entity\Protocolo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Protocolo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Protocolo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Protocolo[]    findAll()
 * @method Protocolo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProtocoloRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Protocolo::class);
    }

    // /**
    //  * @return Protocolo[] Returns an array of Protocolo objects
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
    public function findOneBySomeField($value): ?Protocolo
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
