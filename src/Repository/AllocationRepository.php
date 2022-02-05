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

    /*
    public function findOneBySomeField($value): ?Allocation
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function countaportAllocation($identene)
    {
        $query = $this->createQueryBuilder('p')
        ->select('sum(p.montant)')
        ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
        ->Where('p.antene = :identene')->setParameter('identene',$identene );
        return $query->getQuery()->getOneOrNullResult();

            
    }

    public function countaportAllocationparjour($identene)
    {
        $vurrenttime = new DateTime();
        $year=$vurrenttime->format('Y');
        //dd($vurrenttime->format('Y'));
        $query = $this->createQueryBuilder('p')
        ->select('sum(p.montant),count(p.id)')
        ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
        ->Where('p.antene = :identene')
        ->andWhere('DATE_DIFF(p.createat, CURRENT_DATE()) = 0')
        ->setParameter('identene',$identene );
        //->setParameter('curentdate',$year)
        
        
        return $query->getQuery()->getOneOrNullResult();     
    }


    public function countaportAllocationparjourtype($identene,$type)
    {
        $vurrenttime = new DateTime();
        $year=$vurrenttime->format('Y');
        //dd($vurrenttime->format('Y'));
        $query = $this->createQueryBuilder('p')
        ->select('sum(p.montant),count(p.id)')
        ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
        ->Where('p.antene = :identene')
        ->andWhere('DATE_DIFF(p.createat, CURRENT_DATE()) = 0')
        ->andWhere('p.type = :type')
        ->setParameter('identene',$identene )
        ->setParameter('type',$type );
        //->setParameter('curentdate',$year)
        
        
        return $query->getQuery()->getOneOrNullResult();     
    }

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
        $vurrenttime = new DateTime();
        $year=$vurrenttime->format('Y');
        //dd($vurrenttime->format('Y'));
        $query = $this->createQueryBuilder('p')
        ->select('sum(p.montant)')
        ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
        ->leftJoin('App\Entity\Chambre', 'c', 'WITH', 'p.chambre = c')
        ->Where('p.chambre = :idroom')
        ->andWhere('DATE_DIFF(p.createat, CURRENT_DATE()) = 0')
        ->setParameter('idroom',$idroom );
        //->setParameter('curentdate',$year)
        
        
        return $query->getQuery()->getOneOrNullResult();     
    }

    // public function nombreallocationJourentene($idroom)
    // {
    //     $vurrenttime = new DateTime();
    //     $year=$vurrenttime->format('Y');
    //     //dd($vurrenttime->format('Y'));
    //     $query = $this->createQueryBuilder('p')
    //     ->select('count(p.montant)')
    //     ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
    //     ->leftJoin('App\Entity\Chambre', 'c', 'WITH', 'p.chambre = c')
    //     ->Where('p.chambre = :idroom')
    //     ->andWhere('DATE_DIFF(p.datedebut, CURRENT_DATE()) = 0')
    //     ->setParameter('idroom',$idroom );
    //     //->setParameter('curentdate',$year)
        
        
    //     return $query->getQuery()->getOneOrNullResult();     
    // }


    public function countaportAllocationparmoisForroom($idroom)
    {
        $vurrenttime = new DateTime();
        $year=$vurrenttime->format('Y');
        //dd($vurrenttime->format('Y'));
        $query = $this->createQueryBuilder('p')
        ->select('sum(p.montant)')
        ->leftJoin('App\Entity\Chambre', 'c', 'WITH', 'p.chambre = c')
        ->Where('p.chambre = :idroom')
        ->andWhere('DATE_DIFF(p.createat, CURRENT_DATE()) = 0')
        ->setParameter('idroom',$idroom );
        //->setParameter('curentdate',$year)
        
        
        return $query->getQuery()->getOneOrNullResult();     
    }



    public function countaportAllocationparmois($identene)
    {
        //////initiaisation des fonction de date 
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');
        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
        ///////fin initialisation des fonction de date
        $vurrenttime = new DateTime();
        $year=$vurrenttime->format('Y');
        $mois=$vurrenttime->format('m');
        //dd($mois);
        //dd($vurrenttime->format('Y'));
        $query = $this->createQueryBuilder('p')
        ->select('sum(p.montant)')
        ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
        ->Where('p.antene = :identene')
        ->andWhere('MONTH(p.createat) = :mois')
        ->setParameter('identene',$identene )
        ->setParameter('mois',$mois);
        //->setParameter('curentdate',$year)
        
        
        return $query->getQuery()->getOneOrNullResult();     
    }




    /////////////////////////////////bilan reques
    public function countaportAllocationbymoisantene($identene)
    {
        //////initiaisation des fonction de date 
        $emConfig = $this->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');
        $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
        ///////fin initialisation des fonction de date
        $vurrenttime = new DateTime();
        $year=$vurrenttime->format('Y');
        //$mois=$vurrenttime->format('m');
        //dd($mois);
        //dd($vurrenttime->format('Y'));
        $query = $this->createQueryBuilder('p')
            ->select('MONTH(p.createat) AS mois ,sum(p.montant)')
            ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
            ->Where('p.antene = :identene')
            ->groupBy('mois')
            ->setParameter('identene',$identene );        
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



    // public function getallocationantnebydatebytypetarif($identene,$date)
    // {
    //     $vurrenttime = $date;
    //     $year=$vurrenttime->format('Y');
    //     $mois=$vurrenttime->format('m');
    //     $jour=$vurrenttime->format('d');
    //     //dd($jour);

    //     $emConfig = $this->getEntityManager()->getConfiguration();
    //     $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');
    //     $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
    //     $emConfig->addCustomDatetimeFunction('DAY', 'DoctrineExtensions\Query\Mysql\Day');
    //     $query = $this->createQueryBuilder('p')
    //     ->select('p')
    //     ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
    //     ->Where('p.antene = :identene')
    //     ->andWhere('MONTH(p.datedebut) = :mois')
    //     ->andWhere('DAY(p.datedebut) = :jour')
    //     ->setParameter('identene',$identene )
    //     ->setParameter('jour',$jour)
    //     ->setParameter('mois',$mois);
    //     //->setParameter('curentdate',$year)
    //     return $query->getQuery()->getResult();
    // }



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


    /////////////////////////////////bilan reques
    // public function countaportAllocationbymoisantene($identene,$mois)
    // {
    //     //////initiaisation des fonction de date 
    //     $emConfig = $this->getEntityManager()->getConfiguration();
    //     $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');
    //     $emConfig->addCustomDatetimeFunction('MONTH', 'DoctrineExtensions\Query\Mysql\Month');
    //     ///////fin initialisation des fonction de date
    //     $vurrenttime = new DateTime();
    //     $year=$vurrenttime->format('Y');
    //     //$mois=$vurrenttime->format('m');
    //     //dd($mois);
    //     //dd($vurrenttime->format('Y'));
    //     $query = $this->createQueryBuilder('p')
    //         ->select('sum(p.montant)')
    //         ->leftJoin('App\Entity\Entene', 'u', 'WITH', 'p.antene = u')
    //         ->Where('p.antene = :identene')
    //         ->andWhere('MONTH(p.datedebut) = :mois')
    //         ->setParameter('identene',$identene )
    //         ->setParameter('mois',$mois);        
    //     return $query->getQuery()->getOneOrNullResult();
    // }
    
}
