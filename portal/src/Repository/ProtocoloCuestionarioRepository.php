<?php

namespace App\Repository;

use App\Entity\ProtocoloCuestionario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProtocoloCuestionario|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProtocoloCuestionario|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProtocoloCuestionario[]    findAll()
 * @method ProtocoloCuestionario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProtocoloCuestionarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProtocoloCuestionario::class);
    }

    // /**
    //  * @return ProtocoloCuestionario[] Returns an array of ProtocoloCuestionario objects
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
    public function findOneBySomeField($value): ?ProtocoloCuestionario
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
