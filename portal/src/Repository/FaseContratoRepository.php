<?php

namespace App\Repository;

use App\Entity\FaseContrato;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FaseContrato|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaseContrato|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaseContrato[]    findAll()
 * @method FaseContrato[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaseContratoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaseContrato::class);
    }

    // /**
    //  * @return FaseContrato[] Returns an array of FaseContrato objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FaseContrato
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
