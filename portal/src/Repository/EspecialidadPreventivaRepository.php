<?php

namespace App\Repository;

use App\Entity\EspecialidadPreventiva;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EspecialidadPreventiva|null find($id, $lockMode = null, $lockVersion = null)
 * @method EspecialidadPreventiva|null findOneBy(array $criteria, array $orderBy = null)
 * @method EspecialidadPreventiva[]    findAll()
 * @method EspecialidadPreventiva[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EspecialidadPreventivaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EspecialidadPreventiva::class);
    }

    // /**
    //  * @return EspecialidadPreventiva[] Returns an array of EspecialidadPreventiva objects
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
    public function findOneBySomeField($value): ?EspecialidadPreventiva
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
