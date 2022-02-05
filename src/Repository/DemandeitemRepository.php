<?php

namespace App\Repository;

use App\Entity\Demandeitem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Demandeitem|null find($id, $lockMode = null, $lockVersion = null)
 * @method Demandeitem|null findOneBy(array $criteria, array $orderBy = null)
 * @method Demandeitem[]    findAll()
 * @method Demandeitem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeitemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demandeitem::class);
    }

    // /**
    //  * @return Demandeitem[] Returns an array of Demandeitem objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Demandeitem
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
