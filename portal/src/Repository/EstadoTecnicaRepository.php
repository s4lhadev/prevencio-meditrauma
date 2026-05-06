<?php

namespace App\Repository;

use App\Entity\EstadoTecnica;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EstadoTecnica|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstadoTecnica|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstadoTecnica[]    findAll()
 * @method EstadoTecnica[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstadoTecnicaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstadoTecnica::class);
    }

    // /**
    //  * @return EstadoTecnica[] Returns an array of EstadoTecnica objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EstadoTecnica
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
