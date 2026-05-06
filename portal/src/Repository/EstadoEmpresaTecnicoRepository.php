<?php

namespace App\Repository;

use App\Entity\EstadoEmpresaTecnico;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EstadoEmpresaTecnico|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstadoEmpresaTecnico|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstadoEmpresaTecnico[]    findAll()
 * @method EstadoEmpresaTecnico[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstadoEmpresaTecnicoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstadoEmpresaTecnico::class);
    }

    // /**
    //  * @return EstadoEmpresaTecnico[] Returns an array of EstadoEmpresaTecnico objects
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
    public function findOneBySomeField($value): ?EstadoEmpresaTecnico
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
