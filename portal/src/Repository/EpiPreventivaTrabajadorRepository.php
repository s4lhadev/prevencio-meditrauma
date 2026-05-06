<?php

namespace App\Repository;

use App\Entity\EpiPreventivaTrabajador;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EpiPreventivaTrabajador|null find($id, $lockMode = null, $lockVersion = null)
 * @method EpiPreventivaTrabajador|null findOneBy(array $criteria, array $orderBy = null)
 * @method EpiPreventivaTrabajador[]    findAll()
 * @method EpiPreventivaTrabajador[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpiPreventivaTrabajadorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EpiPreventivaTrabajador::class);
    }

    // /**
    //  * @return EpiPreventivaTrabajador[] Returns an array of EpiPreventivaTrabajador objects
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
    public function findOneBySomeField($value): ?EpiPreventivaTrabajador
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
