<?php

namespace App\Repository;

use App\Entity\UsuarioTecnico;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsuarioTecnico|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsuarioTecnico|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsuarioTecnico[]    findAll()
 * @method UsuarioTecnico[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioTecnicoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsuarioTecnico::class);
    }

    // /**
    //  * @return UsuarioTecnico[] Returns an array of UsuarioTecnico objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UsuarioTecnico
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
