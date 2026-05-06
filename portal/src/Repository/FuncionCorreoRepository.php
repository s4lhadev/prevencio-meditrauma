<?php

namespace App\Repository;

use App\Entity\FuncionCorreo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FuncionCorreo|null find($id, $lockMode = null, $lockVersion = null)
 * @method FuncionCorreo|null findOneBy(array $criteria, array $orderBy = null)
 * @method FuncionCorreo[]    findAll()
 * @method FuncionCorreo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FuncionCorreoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FuncionCorreo::class);
    }

    // /**
    //  * @return FuncionCorreo[] Returns an array of FuncionCorreo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FuncionCorreo
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
