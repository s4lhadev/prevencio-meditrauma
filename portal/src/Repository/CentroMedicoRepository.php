<?php

namespace App\Repository;

use App\Entity\CentroMedico;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CentroMedico|null find($id, $lockMode = null, $lockVersion = null)
 * @method CentroMedico|null findOneBy(array $criteria, array $orderBy = null)
 * @method CentroMedico[]    findAll()
 * @method CentroMedico[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CentroMedicoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CentroMedico::class);
    }

    // /**
    //  * @return CentroMedico[] Returns an array of CentroMedico objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CentroMedico
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
