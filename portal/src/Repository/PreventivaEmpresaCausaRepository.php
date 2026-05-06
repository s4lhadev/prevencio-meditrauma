<?php

namespace App\Repository;

use App\Entity\PreventivaEmpresaCausa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PreventivaEmpresaCausa|null find($id, $lockMode = null, $lockVersion = null)
 * @method PreventivaEmpresaCausa|null findOneBy(array $criteria, array $orderBy = null)
 * @method PreventivaEmpresaCausa[]    findAll()
 * @method PreventivaEmpresaCausa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PreventivaEmpresaCausaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreventivaEmpresaCausa::class);
    }

    // /**
    //  * @return PreventivaEmpresaCausa[] Returns an array of PreventivaEmpresaCausa objects
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
    public function findOneBySomeField($value): ?PreventivaEmpresaCausa
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
