<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }


    public function filterByDate($debut, $fin, $id, $etat)
    {
        $query = $this->createQueryBuilder('r');        
        
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');

        $query = $query->andWhere('r.antene =:id')->setParameter('id', $id);

        if($etat){
            $query = $query->andWhere('r.etat =:etat')->setParameter('etat', $etat);
        }
        if($debut && $fin && $debut <= $fin){
            
            $query->andWhere('DATE(r.createat) BETWEEN :debut AND :fin')                  
                  ->setParameter('debut', $debut)
                  ->setParameter('fin', $fin);
        }            
          return  $query->orderBy('r.createat', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
