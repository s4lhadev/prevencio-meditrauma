<?php

namespace App\Repository;

use App\Entity\MaquinaEmpresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MaquinaEmpresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaquinaEmpresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaquinaEmpresa[]    findAll()
 * @method MaquinaEmpresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaquinaEmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaquinaEmpresa::class);
    }

    // /**
    //  * @return MaquinaEmpresa[] Returns an array of MaquinaEmpresa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MaquinaEmpresa
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
