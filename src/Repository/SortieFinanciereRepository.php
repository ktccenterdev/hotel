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
    
    public function findByTypeBeneficiaire($beneficiaire, $idAntenne=null, $debut=null, $fin=null, $today=null)
    {
        
        $query = $this->createQueryBuilder('s');
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');

        $query->join('App\Entity\Beneficiaire', 'b', 'WITH', 's.beneficiaire = b')
            ->andWhere('b.type =:beneficiaire')
            ->setParameter('beneficiaire', $beneficiaire);       
        
        if($idAntenne){
            $query->andWhere('s.antenne =:idAntenne')->setParameter('idAntenne', $idAntenne);
        }

        if($debut && $fin){
            $query->andWhere('DATE(s.createdAt) BETWEEN :debut AND :fin')                  
                  ->setParameter('debut', $debut)
                  ->setParameter('fin', $fin);
        }elseif($today){
            $query->andWhere('DATE(s.createdAt) =:jour')->setParameter('jour', $today);
        }
         return $query->orderBy('s.id', 'ASC')
                    ->getQuery()
                    ->getResult();        
    }

    public function findSortiesBy($debut=null, $fin=null, $antenne=null)
    {
        $query = $this->createQueryBuilder('a');           
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');
                      
        if($antenne){
            $query->leftJoin('App\Entity\Entene','e', 'WITH', 'a.antenne = e')
                  ->andWhere('e.id =:id')->setParameter('id', $antenne->getId());
        }
        if($debut && $fin){
            $query->andWhere('DATE(a.createdAt) BETWEEN :debut AND :fin')                  
                  ->setParameter('debut', $debut)
                  ->setParameter('fin', $fin);
        }        
        return $query->getQuery()->getResult();
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
