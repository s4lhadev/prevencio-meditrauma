<?php

namespace App\Repository;

use App\Entity\EmpresaMemoria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmpresaMemoria|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmpresaMemoria|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmpresaMemoria[]    findAll()
 * @method EmpresaMemoria[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpresaMemoriaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmpresaMemoria::class);
    }

    // /**
    //  * @return EmpresaMemoria[] Returns an array of EmpresaMemoria objects
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
    public function findOneBySomeField($value): ?EmpresaMemoria
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
