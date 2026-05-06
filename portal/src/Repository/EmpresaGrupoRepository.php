<?php

namespace App\Repository;

use App\Entity\EmpresaGrupo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmpresaGrupo|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmpresaGrupo|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmpresaGrupo[]    findAll()
 * @method EmpresaGrupo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpresaGrupoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmpresaGrupo::class);
    }

    // /**
    //  * @return EmpresaGrupo[] Returns an array of EmpresaGrupo objects
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
    public function findOneBySomeField($value): ?EmpresaGrupo
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
