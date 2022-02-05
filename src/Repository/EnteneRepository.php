<?php

namespace App\Repository;

use App\Entity\Entene;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entene|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entene|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entene[]    findAll()
 * @method Entene[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnteneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entene::class);
    }

    // /**
    //  * @return Entene[] Returns an array of Entene objects
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
    public function findOneBySomeField($value): ?Entene
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
