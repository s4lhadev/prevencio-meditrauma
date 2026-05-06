<?php

namespace App\Repository;

use App\Entity\MaquinaGenerica;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MaquinaGenerica|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaquinaGenerica|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaquinaGenerica[]    findAll()
 * @method MaquinaGenerica[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaquinaGenericaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaquinaGenerica::class);
    }

    // /**
    //  * @return MaquinaGenerica[] Returns an array of MaquinaGenerica objects
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
    public function findOneBySomeField($value): ?MaquinaGenerica
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
