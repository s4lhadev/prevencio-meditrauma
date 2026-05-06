<?php

namespace App\Repository;

use App\Entity\ServicioPrevencion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ServicioPrevencion|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServicioPrevencion|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServicioPrevencion[]    findAll()
 * @method ServicioPrevencion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServicioPrevencionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServicioPrevencion::class);
    }

    // /**
    //  * @return ServicioPrevencion[] Returns an array of ServicioPrevencion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ServicioPrevencion
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
