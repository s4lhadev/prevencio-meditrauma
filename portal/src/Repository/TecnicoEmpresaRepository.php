<?php

namespace App\Repository;

use App\Entity\TecnicoEmpresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TecnicoEmpresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method TecnicoEmpresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method TecnicoEmpresa[]    findAll()
 * @method TecnicoEmpresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TecnicoEmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TecnicoEmpresa::class);
    }

    // /**
    //  * @return TecnicoEmpresa[] Returns an array of TecnicoEmpresa objects
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
    public function findOneBySomeField($value): ?TecnicoEmpresa
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
