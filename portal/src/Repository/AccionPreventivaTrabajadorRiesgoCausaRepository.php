<?php

namespace App\Repository;

use App\Entity\AccionPreventivaTrabajadorRiesgoCausa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccionPreventivaTrabajadorRiesgoCausa|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccionPreventivaTrabajadorRiesgoCausa|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccionPreventivaTrabajadorRiesgoCausa[]    findAll()
 * @method AccionPreventivaTrabajadorRiesgoCausa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccionPreventivaTrabajadorRiesgoCausaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccionPreventivaTrabajadorRiesgoCausa::class);
    }

    // /**
    //  * @return AccionPreventivaTrabajadorRiesgoCausa[] Returns an array of AccionPreventivaTrabajadorRiesgoCausa objects
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
    public function findOneBySomeField($value): ?AccionPreventivaTrabajadorRiesgoCausa
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
