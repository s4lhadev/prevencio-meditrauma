<?php

namespace App\Repository;

use App\Entity\EmpresaNotificacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmpresaNotificacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmpresaNotificacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmpresaNotificacion[]    findAll()
 * @method EmpresaNotificacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpresaNotificacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmpresaNotificacion::class);
    }

    // /**
    //  * @return EmpresaNotificacion[] Returns an array of EmpresaNotificacion objects
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
    public function findOneBySomeField($value): ?EmpresaNotificacion
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
