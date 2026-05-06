<?php

namespace App\Repository;

use App\Entity\EstadoEmpresaSalud;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EstadoEmpresaSalud|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstadoEmpresaSalud|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstadoEmpresaSalud[]    findAll()
 * @method EstadoEmpresaSalud[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstadoEmpresaSaludRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstadoEmpresaSalud::class);
    }

    // /**
    //  * @return EstadoEmpresa[] Returns an array of EstadoEmpresa objects
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
    public function findOneBySomeField($value): ?EstadoEmpresa
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
