<?php

namespace App\Repository;

use App\Entity\Allocation;
use App\Entity\Entene;
use \Datetime;
use Doctrine\ORM\Query\Expr\GroupBy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Allocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Allocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Allocation[]    findAll()
 * @method Allocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AllocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Allocation::class);
    }

    public function bilanPeriodique($id, $debut, $fin, $jour)
    {
        $query = $this->createQueryBuilder('a');      
        $queryMontant = $this->createQueryBuilder('a');      
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');
        
        $query->select('u') 
              ->leftJoin('App\Entity\User','u', 'WITH', 'a.operateur = u')
              ->andWhere('a.antene =:id')->setParameter('id', $id)
              ->distinct();
        
        $queryMontant->select('sum(a.montant) as montant') 
              ->andWhere('a.antene =:id')
              ->andWhere('a.operateur =:operateur')
              ->andWhere('a.type =:type')
              ->setParameter('id', $id);

        if($debut && $fin){
            $query->andWhere('DATE(a.createat) BETWEEN :debut AND :fin')                  
                  ->setParameter('debut', $debut)
                  ->setParameter('fin', $fin);
            $queryMontant->andWhere('DATE(a.createat) BETWEEN :debut AND :fin')                  
                  ->setParameter('debut', $debut)
                  ->setParameter('fin', $fin);
        }elseif($jour){
            $query->andWhere('DATE(a.createat) =:jour')->setParameter('jour', $jour);
            $queryMontant->andWhere('DATE(a.createat) =:jour')->setParameter('jour', $jour);
        }

        $operateurs = $query->getQuery()->getResult();
        $entrees = array();
        $total = 0;
        foreach ($operateurs as $operateur) {
            $queryMontant->setParameter('operateur', $operateur->getId());
            $sieste = $queryMontant->setParameter('type', 'SIESTE')->getQuery()->getOneOrNullResult()['montant'];
            $nuitee = $queryMontant->setParameter('type', 'NUITEE')->getQuery()->getOneOrNullResult()['montant'];
            $entrees[] = array(
                'operateur' => $operateur,
                'sieste' => $sieste,
                'nuitee' => $nuitee
            );
        }   
        return $entrees;
    }

    public function monBilan($antennes, $user, $debut, $fin, $jour)
    {
        $queryMontant = $this->createQueryBuilder('a');      
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');
        
        $entrees = array();
        
        $queryMontant->select('sum(a.montant) as montant') 
              ->andWhere('a.antene =:id')
              ->andWhere('a.operateur =:operateur')
              ->andWhere('a.type =:type')
              ->setParameter('operateur', $user->getId());

        if($debut && $fin){
            $queryMontant->andWhere('DATE(a.createat) BETWEEN :debut AND :fin')                  
                  ->setParameter('debut', $debut)
                  ->setParameter('fin', $fin);
        }elseif($jour){
            $queryMontant->andWhere('DATE(a.createat) =:jour')->setParameter('jour', $jour);
        }
        foreach ($antennes as $antenne) {
            $queryMontant->setParameter('id', $antenne->getId());
            $sieste = $queryMontant->setParameter('type', 'SIESTE')->getQuery()->getOneOrNullResult()['montant'];
            $nuitee = $queryMontant->setParameter('type', 'NUITEE')->getQuery()->getOneOrNullResult()['montant'];
            $entrees[] =  array(
                'antenne' => $antenne,
                'sieste' => $sieste,
                'nuitee' => $nuitee
            );
        }
        return $entrees;
    }

    public function detailsEntrees($idantenne, $iduser, $jour, $debut, $fin)
    {
        $query = $this->createQueryBuilder('a')
        ->andWhere('a.antene =:idantenne')
        ->andWhere('a.operateur =:operateur')
        ->setParameter('idantenne',$idantenne )
        ->setParameter('operateur',$iduser );

        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');

        if($debut && $fin){
            $query->andWhere('DATE(a.createat) BETWEEN :debut AND :fin')                  
                  ->setParameter('debut', $debut)
                  ->setParameter('fin', $fin);
        }elseif($jour){
            $query->andWhere('DATE(a.createat) =:jour')->setParameter('jour', $jour);
        }        
        return $query->getQuery()->getResult();     
    }

    public function findAllocationsBy($debut=null, $fin=null, $antenne=null)
    {
        $query = $this->createQueryBuilder('a');           
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');
                      
        if($antenne){
            $query->leftJoin('App\Entity\Entene','e', 'WITH', 'a.antene = e')
                  ->andWhere('e.id =:id')->setParameter('id', $antenne->getId());
        }
        if($debut && $fin){
            $query->andWhere('DATE(a.createat) BETWEEN :debut AND :fin')                  
                  ->setParameter('debut', $debut)
                  ->setParameter('fin', $fin);
        }        
        return $query->getQuery()->getResult();
    }


    public function countaportAllocation($identene = null)
    {
        $query = $this->createQueryBuilder('p')
        ->select('sum(p.montant) as montant')
        ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u');
        if($identene){
            $query->Where('p.antene = :identene')
                  ->setParameter('identene',$identene );
        }
        return floatval($query->getQuery()->getOneOrNullResult()['montant']);

            
    }

    public function countaportAllocationparjour($identene=null)
    {
        $query = $this->createQueryBuilder('p')
        ->select('sum(p.montant) as montant')
        ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
        ->andWhere('DATE_DIFF(p.createat, CURRENT_DATE()) = 0');        
        if($identene){
            $query->andWhere('p.antene = :identene')
            ->setParameter('identene',$identene ); 
        }
        return floatval($query->getQuery()->getOneOrNullResult()['montant']);     
    }


    /*public function countaportAllocationparjourtype($identene,$type=null)
    {
        $query = $this->createQueryBuilder('p')
        ->select('sum(p.montant) as montant')
        ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
        ->Where('p.antene = :identene')
        ->andWhere('DATE_DIFF(p.createat, CURRENT_DATE()) = 0')
        // ->andWhere('p.type = :type')
        ->setParameter('identene',$identene );
        // ->setParameter('type',$type );
        
        return $query->getQuery()->getOneOrNullResult()['montant'];     
    }*/

    public function countallocaionbyday($identene)
    {
        $vurrenttime = new DateTime();
        $year=$vurrenttime->format('Y');
        //dd($vurrenttime->format('Y'));
        $query = $this->createQueryBuilder('p')
        ->select('count(p.montant)')
        ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
        ->Where('p.antene = :identene')
        ->andWhere('DATE_DIFF(p.createat, CURRENT_DATE()) = 0')
        ->setParameter('identene',$identene );
        //->setParameter('curentdate',$year)
        
        
        return $query->getQuery()->getOneOrNullResult();     
    }


    public function getallocationofday($identene)
    {
        $vurrenttime = new DateTime();
        $year=$vurrenttime->format('Y');
        //dd($vurrenttime->format('Y'));
        $query = $this->createQueryBuilder('p')
        ->select('p')
        ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
        ->Where('p.antene = :identene')
        ->andWhere('DATE_DIFF(p.createat, CURRENT_DATE()) = 0')
        ->setParameter('identene',$identene );
        
        return $query->getQuery()->getOneOrNullResult();     
    }

    



    public function countaportAllocationparjourForroom($idroom)
    {
        $query = $this->createQueryBuilder('p')
        ->select('sum(p.montant) as montant')
        // ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
        ->leftJoin('App\Entity\Chambre', 'c', 'WITH', 'p.chambre = c')
        ->Where('p.chambre = :idroom')
        ->andWhere('DATE_DIFF(p.createat, CURRENT_DATE()) = 0')
        ->setParameter('idroom',$idroom );
        return floatval($query->getQuery()->getOneOrNullResult()['montant']);     
    }


    public function countaportAllocationparmoisForroom($idroom)
    {
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
        $mois = date('m');
        $query = $this->createQueryBuilder('p')
        ->select('sum(p.montant) as montant')
        ->leftJoin('App\Entity\Chambre', 'c', 'WITH', 'p.chambre = c')
        ->Where('p.chambre = :idroom')
        ->andWhere('MONTH(p.createat) =:mois')
        ->setParameter('idroom',$idroom )
        ->setParameter('mois', $mois);
        return floatval($query->getQuery()->getOneOrNullResult()['montant']);     
    }


    public function montantAllocationChambre($idroom)
    {
        $query = $this->createQueryBuilder('p')
        ->select('sum(p.montant) as montant')
        ->leftJoin('App\Entity\Chambre', 'c', 'WITH', 'p.chambre = c')
        ->Where('p.chambre = :idroom')
        ->setParameter('idroom',$idroom );
        return floatval($query->getQuery()->getOneOrNullResult()['montant']);     
    }



    public function countaportAllocationparmois($identene=null)
    {
        //////initiaisation des fonction de date 
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');
        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
        ///////fin initialisation des fonction de date
        $vurrenttime = new DateTime();
        $mois=$vurrenttime->format('m');
        $query = $this->createQueryBuilder('p')
        ->select('sum(p.montant) as montant')
        ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
        ->andWhere('MONTH(p.createat) = :mois')
        ->setParameter('mois',$mois);
        if($identene){
            $query->andWhere('p.antene = :identene')
            ->setParameter('identene',$identene );
        }        
        return  floatval($query->getQuery()->getOneOrNullResult()['montant']);   
    }




    /////////////////////////////////bilan reques
    public function countaportAllocationbymoisantene($identene=null)
    {
        //////initiaisation des fonction de date 
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');
        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');

        $query = $this->createQueryBuilder('p')
            ->select('MONTH(p.createat) AS mois ,sum(p.montant) as montant')
            ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')            
            ->groupBy('mois');
            if($identene){
                $query->Where('p.antene = :identene')
                ->setParameter('identene',$identene );   
            }
            return $query->getQuery()->getResult();
    }


    public function getallocationantnebydate($identene,$date)
    {
        $vurrenttime = $date;
        $year=$vurrenttime->format('Y');
        $mois=$vurrenttime->format('m');
        $jour=$vurrenttime->format('d');
        //dd($jour);

        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');
        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
        $emConfig->addCustomDatetimeFunction('DAY', 'DoctrineExtensions\Query\Mysql\Day');
        $query = $this->createQueryBuilder('p')
        ->select('p')
        ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
        ->Where('p.antene = :identene')
        ->andWhere('MONTH(p.createat) = :mois')
        ->andWhere('DAY(p.createat) = :jour')
        ->setParameter('identene',$identene )
        ->setParameter('jour',$jour)
        ->setParameter('mois',$mois);
        //->setParameter('curentdate',$year)
        return $query->getQuery()->getResult();
    }


    public function getallocationantnebydateofuser($identene,$date,$iduser)
    {
        $vurrenttime = $date;
        $year=$vurrenttime->format('Y');
        $mois=$vurrenttime->format('m');
        $jour=$vurrenttime->format('d');
        //dd($jour);

        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');
        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
        $emConfig->addCustomDatetimeFunction('DAY', 'DoctrineExtensions\Query\Mysql\Day');
        $query = $this->createQueryBuilder('p')
        ->select('p')
        ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
        ->leftJoin('App\Entity\User', 'o', 'WITH', 'p.operateur = o')
        ->Where('p.antene = :identene')
        ->andWhere('p.operateur = :operateur')
        ->andWhere('MONTH(p.createat) = :mois')
        ->andWhere('DAY(p.createat) = :jour')
        ->setParameter('identene',$identene )
        ->setParameter('jour',$jour)
        ->setParameter('operateur',$iduser)
        ->setParameter('mois',$mois);
        //->setParameter('curentdate',$year)
        return $query->getQuery()->getResult();
    }
    ////////////////////////////////end bilan 


}
