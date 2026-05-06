<?php

namespace App\Repository;

use App\Entity\TrabajadorEmpresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TrabajadorEmpresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrabajadorEmpresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrabajadorEmpresa[]    findAll()
 * @method TrabajadorEmpresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrabajadorEmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrabajadorEmpresa::class);
    }

    // /**
    //  * @return TrabajadorEmpresa[] Returns an array of TrabajadorEmpresa objects
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
    public function findOneBySomeField($value): ?TrabajadorEmpresa
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
