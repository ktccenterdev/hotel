<?php

namespace App\Repository;

use App\Entity\Entreitem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entreitem|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entreitem|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entreitem[]    findAll()
 * @method Entreitem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntreitemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entreitem::class);
    }

    // /**
    //  * @return Entreitem[] Returns an array of Entreitem objects
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
    public function findOneBySomeField($value): ?Entreitem
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
