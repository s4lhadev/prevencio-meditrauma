<?php

namespace App\Repository;

use App\Entity\TarifaRevisionMedica;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TarifaRevisionMedica|null find($id, $lockMode = null, $lockVersion = null)
 * @method TarifaRevisionMedica|null findOneBy(array $criteria, array $orderBy = null)
 * @method TarifaRevisionMedica[]    findAll()
 * @method TarifaRevisionMedica[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TarifaRevisionMedicaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TarifaRevisionMedica::class);
    }

    // /**
    //  * @return TarifaRevisionMedica[] Returns an array of TarifaRevisionMedica objects
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
    public function findOneBySomeField($value): ?TarifaRevisionMedica
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
