<?php

namespace App\Repository;

use App\Entity\Sortirstock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortirstock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortirstock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortirstock[]    findAll()
 * @method Sortirstock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortirstockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortirstock::class);
    }

    // /**
    //  * @return Sortirstock[] Returns an array of Sortirstock objects
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
    public function findOneBySomeField($value): ?Sortirstock
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
