<?php

namespace App\Repository;

use App\Entity\EstadoSalud;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EstadoSalud|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstadoSalud|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstadoSalud[]    findAll()
 * @method EstadoSalud[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstadoSaludRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstadoSalud::class);
    }

    // /**
    //  * @return EstadoSalud[] Returns an array of EstadoSalud objects
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
    public function findOneBySomeField($value): ?EstadoSalud
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
