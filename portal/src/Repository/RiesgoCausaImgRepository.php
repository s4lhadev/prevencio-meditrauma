<?php

namespace App\Repository;

use App\Entity\RiesgoCausaImg;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RiesgoCausaImg|null find($id, $lockMode = null, $lockVersion = null)
 * @method RiesgoCausaImg|null findOneBy(array $criteria, array $orderBy = null)
 * @method RiesgoCausaImg[]    findAll()
 * @method RiesgoCausaImg[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RiesgoCausaImgRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RiesgoCausaImg::class);
    }

    // /**
    //  * @return RiesgoCausaImg[] Returns an array of RiesgoCausaImg objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RiesgoCausaImg
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
