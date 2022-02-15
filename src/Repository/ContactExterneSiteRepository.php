<?php

namespace App\Repository;

use App\Entity\ContactExterneSite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContactExterneSite|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactExterneSite|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactExterneSite[]    findAll()
 * @method ContactExterneSite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactExterneSiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactExterneSite::class);
    }

    // /**
    //  * @return ContactExterneSite[] Returns an array of ContactExterneSite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ContactExterneSite
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
