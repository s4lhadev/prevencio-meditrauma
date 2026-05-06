<?php

namespace App\Repository;

use App\Entity\PreventivaTrabajadorCausa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PreventivaTrabajadorCausa|null find($id, $lockMode = null, $lockVersion = null)
 * @method PreventivaTrabajadorCausa|null findOneBy(array $criteria, array $orderBy = null)
 * @method PreventivaTrabajadorCausa[]    findAll()
 * @method PreventivaTrabajadorCausa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PreventivaTrabajadorCausaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreventivaTrabajadorCausa::class);
    }

    // /**
    //  * @return PreventivaTrabajadorCausa[] Returns an array of PreventivaTrabajadorCausa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PreventivaTrabajadorCausa
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
