<?php

namespace App\Repository;

use App\Entity\PreventivaEmpresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PreventivaEmpresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method PreventivaEmpresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method PreventivaEmpresa[]    findAll()
 * @method PreventivaEmpresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PreventivaEmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreventivaEmpresa::class);
    }

    // /**
    //  * @return PreventivaEmpresa[] Returns an array of PreventivaEmpresa objects
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
    public function findOneBySomeField($value): ?PreventivaEmpresa
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
