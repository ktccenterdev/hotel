<?php

namespace App\Repository;

use App\Entity\SortieFinanciere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SortieFinanciere|null find($id, $lockMode = null, $lockVersion = null)
 * @method SortieFinanciere|null findOneBy(array $criteria, array $orderBy = null)
 * @method SortieFinanciere[]    findAll()
 * @method SortieFinanciere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieFinanciereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SortieFinanciere::class);
    }

    // /**
    //  * @return SortieFinanciere[] Returns an array of SortieFinanciere objects
    //  */
    
    public function filtrer($operateur, $antenne, $debut, $fin)
    {
        
        $query = $this->createQueryBuilder('s');
        if($operateur){
            $query->join('App\Entity\User', 'u', 'WITH', 's.operateur = u')
            ->andWhere('u.id = :operateur')
            ->setParameter('operateur', $operateur);
        }
        if($antenne){
            $query->join('App\Entity\Entene', 'a', 'WITH', 's.antenne = a')
            ->andWhere('a.id = :antenne')
            ->setParameter('antenne', $antenne);
        }
        if($debut !== null && $fin !== null){
            $query->where('e.fecha BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut->format('Y-m-d'))
            ->setParameter('fin', $fin->format('Y-m-d'));
        }            
         return $query->orderBy('s.id', 'ASC')
        //->setMaxResults(10)
        ->getQuery()
        ->getResult();
        
    }
    
    public function findByTypeBeneficiaire($beneficiaire)
    {
        
        $query = $this->createQueryBuilder('s');
            $query->join('App\Entity\Beneficiaire', 'b', 'WITH', 's.beneficiaire = b')
            ->andWhere('b.type = :beneficiaire')
            ->setParameter('beneficiaire', $beneficiaire);       
       
         return $query->orderBy('s.id', 'ASC')
        ->getQuery()
        ->getResult();
        
    }


    /*
    public function findOneBySomeField($value): ?SortieFinanciere
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