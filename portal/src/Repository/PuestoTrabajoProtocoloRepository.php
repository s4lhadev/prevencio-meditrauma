<?php

namespace App\Repository;

use App\Entity\PuestoTrabajoProtocolo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PuestoTrabajoProtocolo|null find($id, $lockMode = null, $lockVersion = null)
 * @method PuestoTrabajoProtocolo|null findOneBy(array $criteria, array $orderBy = null)
 * @method PuestoTrabajoProtocolo[]    findAll()
 * @method PuestoTrabajoProtocolo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PuestoTrabajoProtocoloRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PuestoTrabajoProtocolo::class);
    }

    // /**
    //  * @return PuestoTrabajoProtocolo[] Returns an array of PuestoTrabajoProtocolo objects
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
    public function findOneBySomeField($value): ?PuestoTrabajoProtocolo
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
