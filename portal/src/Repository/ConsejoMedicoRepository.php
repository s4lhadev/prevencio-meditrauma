<?php

namespace App\Repository;

use App\Entity\ConsejoMedico;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConsejoMedico|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConsejoMedico|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConsejoMedico[]    findAll()
 * @method ConsejoMedico[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsejoMedicoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsejoMedico::class);
    }

    // /**
    //  * @return ConsejoMedico[] Returns an array of ConsejoMedico objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ConsejoMedico
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
