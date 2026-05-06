<?php

namespace App\Repository;

use App\Entity\AccionPreventivaEmpresaRiesgoCausa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccionPreventivaEmpresaRiesgoCausa|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccionPreventivaEmpresaRiesgoCausa|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccionPreventivaEmpresaRiesgoCausa[]    findAll()
 * @method AccionPreventivaEmpresaRiesgoCausa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccionPreventivaEmpresaRiesgoCausaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccionPreventivaEmpresaRiesgoCausa::class);
    }

    // /**
    //  * @return AccionPreventivaRiesgoCausa[] Returns an array of AccionPreventivaRiesgoCausa objects
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
    public function findOneBySomeField($value): ?AccionPreventivaRiesgoCausa
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
