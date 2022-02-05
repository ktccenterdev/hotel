<?php

namespace App\Repository;

use App\Entity\Entrestock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entrestock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entrestock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entrestock[]    findAll()
 * @method Entrestock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntrestockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entrestock::class);
    }

    // /**
    //  * @return Entrestock[] Returns an array of Entrestock objects
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
    public function findOneBySomeField($value): ?Entrestock
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
