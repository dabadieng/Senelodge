<?php

namespace App\Repository;

use App\Entity\SearchAd;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SearchAd|null find($id, $lockMode = null, $lockVersion = null)
 * @method SearchAd|null findOneBy(array $criteria, array $orderBy = null)
 * @method SearchAd[]    findAll()
 * @method SearchAd[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SearchAdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchAd::class);
    }

    // /**
    //  * @return SearchAd[] Returns an array of SearchAd objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SearchAd
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
