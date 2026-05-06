<?php

namespace App\Repository;

use App\Entity\EmpresaCertificacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmpresaCertificacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmpresaCertificacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmpresaCertificacion[]    findAll()
 * @method EmpresaCertificacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpresaCertificacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmpresaCertificacion::class);
    }

    // /**
    //  * @return EmpresaCertificacion[] Returns an array of EmpresaCertificacion objects
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
    public function findOneBySomeField($value): ?EmpresaCertificacion
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
