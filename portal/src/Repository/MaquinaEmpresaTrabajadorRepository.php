<?php

namespace App\Repository;

use App\Entity\MaquinaEmpresaTrabajador;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MaquinaEmpresaTrabajador|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaquinaEmpresaTrabajador|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaquinaEmpresaTrabajador[]    findAll()
 * @method MaquinaEmpresaTrabajador[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaquinaEmpresaTrabajadorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaquinaEmpresaTrabajador::class);
    }

    // /**
    //  * @return MaquinaEmpresaTrabajador[] Returns an array of MaquinaEmpresaTrabajador objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MaquinaEmpresaTrabajador
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
