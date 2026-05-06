<?php

namespace App\Repository;

use App\Entity\EmpresaAccidenteLaboral;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmpresaAccidenteLaboral|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmpresaAccidenteLaboral|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmpresaAccidenteLaboral[]    findAll()
 * @method EmpresaAccidenteLaboral[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpresaAccidenteLaboralRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmpresaAccidenteLaboral::class);
    }

    // /**
    //  * @return EmpresaAccidenteLaboral[] Returns an array of EmpresaAccidenteLaboral objects
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
    public function findOneBySomeField($value): ?EmpresaAccidenteLaboral
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
