<?php

namespace App\Repository;

use App\Entity\ZonaTrabajoMaquinaEmpresa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZonaTrabajoMaquinaEmpresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZonaTrabajoMaquinaEmpresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZonaTrabajoMaquinaEmpresa[]    findAll()
 * @method ZonaTrabajoMaquinaEmpresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZonaTrabajoMaquinaEmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ZonaTrabajoMaquinaEmpresa::class);
    }

    // /**
    //  * @return ZonaTrabajoMaquinaEmpresa[] Returns an array of ZonaTrabajoMaquinaEmpresa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('z.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ZonaTrabajoMaquinaEmpresa
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
