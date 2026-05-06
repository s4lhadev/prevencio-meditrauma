<?php

namespace App\Repository;

use App\Entity\AgendaTecnico;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendaTecnico|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaTecnico|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaTecnico[]    findAll()
 * @method AgendaTecnico[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaTecnicoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaTecnico::class);
    }

    // /**
    //  * @return AgendaTecnico[] Returns an array of AgendaTecnico objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AgendaTecnico
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
