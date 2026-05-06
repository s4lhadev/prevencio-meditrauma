<?php

namespace App\Repository;

use App\Entity\ZonaTrabajoMaquinaGenerica;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZonaTrabajoMaquinaGenerica|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZonaTrabajoMaquinaGenerica|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZonaTrabajoMaquinaGenerica[]    findAll()
 * @method ZonaTrabajoMaquinaGenerica[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZonaTrabajoMaquinaGenericaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ZonaTrabajoMaquinaGenerica::class);
    }

    // /**
    //  * @return ZonaTrabajoMaquinaGenerica[] Returns an array of ZonaTrabajoMaquinaGenerica objects
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
    public function findOneBySomeField($value): ?ZonaTrabajoMaquinaGenerica
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
