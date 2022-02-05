<?php

namespace App\Repository;

use App\Entity\Typechambre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Typechambre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Typechambre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Typechambre[]    findAll()
 * @method Typechambre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypechambreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Typechambre::class);
    }

    // /**
    //  * @return Typechambre[] Returns an array of Typechambre objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Typechambre
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
