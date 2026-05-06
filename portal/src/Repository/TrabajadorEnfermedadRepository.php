<?php

namespace App\Repository;

use App\Entity\TrabajadorEnfermedad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TrabajadorEnfermedad|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrabajadorEnfermedad|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrabajadorEnfermedad[]    findAll()
 * @method TrabajadorEnfermedad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrabajadorEnfermedadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrabajadorEnfermedad::class);
    }

    // /**
    //  * @return TrabajadorEnfermedad[] Returns an array of TrabajadorEnfermedad objects
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
    public function findOneBySomeField($value): ?TrabajadorEnfermedad
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
