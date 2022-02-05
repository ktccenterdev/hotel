<?php

namespace App\Repository;

use App\Entity\Demandeaprovisoinment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Demandeaprovisoinment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Demandeaprovisoinment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Demandeaprovisoinment[]    findAll()
 * @method Demandeaprovisoinment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeaprovisoinmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demandeaprovisoinment::class);
    }

    // /**
    //  * @return Demandeaprovisoinment[] Returns an array of Demandeaprovisoinment objects
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
    public function findOneBySomeField($value): ?Demandeaprovisoinment
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
