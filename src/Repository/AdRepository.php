<?php

namespace App\Repository;

use App\Entity\Ad;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use App\Entity\SearchAd;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Ad|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ad|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ad[]    findAll()
 * @method Ad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ad::class);
    }

    public function findBestAds($limit)
    {
        return $this->createQueryBuilder('a')
            ->select('a as annonce, AVG(c.rating) as avgRatings')
            ->join('a.descriptions', 'c')
            ->groupBy('a')
            ->orderBy('avgRatings', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Filtre les annonces 
     *
     * @param SearchAd $search
     * @return Query
     */
    public function findAllFiltreQuery(SearchAd $search): Query
    {

        $query = $this->createQueryBuilder('a');
        //$query = $this->findFiltreQuery(); 

        if ($search->getMaxPrice()) {
            $query = $query
                ->andWhere('a.price <= :maxPrice')
                ->setParameter('maxPrice', $search->getMaxPrice());
        }

        if ($search->getRooms()) {
            $query = $query
                ->andWhere('a.rooms = :rooms')
                ->setParameter('rooms', $search->getRooms());
        }

        if ($search->getLocalisation()->count() > 0) {
            foreach ($search->getLocalisation() as $key => $name) {
                $query = $query
                    ->andWhere("a.localisation IN (:name)")
                    ->setParameter("name", $name);
            }
        }



        return $query->getQuery(); 
        //->getResult();
    }

    // /**
    //  * @return Ad[] Returns an array of Ad objects
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
    public function findOneBySomeField($value): ?Ad
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
