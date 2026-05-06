<?php

namespace App\Repository;

use App\Entity\ServicioContratado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ServicioContratado|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServicioContratado|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServicioContratado[]    findAll()
 * @method ServicioContratado[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServicioContratadoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServicioContratado::class);
    }

    // /**
    //  * @return ServicioContratado[] Returns an array of ServicioContratado objects
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
    public function findOneBySomeField($value): ?ServicioContratado
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
