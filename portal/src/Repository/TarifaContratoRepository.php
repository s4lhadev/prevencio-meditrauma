<?php

namespace App\Repository;

use App\Entity\TarifaContrato;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TarifaContrato|null find($id, $lockMode = null, $lockVersion = null)
 * @method TarifaContrato|null findOneBy(array $criteria, array $orderBy = null)
 * @method TarifaContrato[]    findAll()
 * @method TarifaContrato[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TarifaContratoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TarifaContrato::class);
    }

    // /**
    //  * @return TarifaContrato[] Returns an array of TarifaContrato objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TarifaContrato
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
