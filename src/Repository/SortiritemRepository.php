<?php

namespace App\Repository;

use App\Entity\Sortiritem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortiritem|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortiritem|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortiritem[]    findAll()
 * @method Sortiritem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortiritemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortiritem::class);
    }

    // /**
    //  * @return Sortiritem[] Returns an array of Sortiritem objects
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
    public function findOneBySomeField($value): ?Sortiritem
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
