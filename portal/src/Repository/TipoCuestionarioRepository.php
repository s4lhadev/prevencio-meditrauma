<?php

namespace App\Repository;

use App\Entity\TipoCuestionario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TipoCuestionario|null find($id, $lockMode = null, $lockVersion = null)
 * @method TipoCuestionario|null findOneBy(array $criteria, array $orderBy = null)
 * @method TipoCuestionario[]    findAll()
 * @method TipoCuestionario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TipoCuestionarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TipoCuestionario::class);
    }

    // /**
    //  * @return TipoCuestionario[] Returns an array of TipoCuestionario objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TipoCuestionario
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
