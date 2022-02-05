<?php

namespace App\Controller;

use \Datetime;
use \DateInterval;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Tarif;
use App\Entity\Compte;
use App\Entity\Entene;
use DateTimeImmutable;
use App\Entity\Chambre;
use App\Entity\Allocation;
use App\Entity\Typechambre;
use App\Entity\SortieFinanciere;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BilanController extends DefaultController
{

    /**
     * @Route("/bilanjournalier", name="bilanjournalier", methods={"GET"})
     */
    public function bilanjournalier(Request $request)
    {
        $link="bilanjournalier";
        $user = $this->getUser();
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $operateur = $request->get('operateur');
        $today = $request->get('today');
        $bilans = array();
        $totaux = array();
        try {
            if($user){
                $antenne = $user->getAntene();
                if($user->getIsadmin()){
                    $antennes = $this->getAllAntennes();
                    foreach ($antennes as $antenne) {
                        $listes = $this->em->getRepository(Allocation::class)->bilanPeriodique($antenne->getId(), $debut, $fin, $today);
                        $total = array_sum(array_column($listes, 'nuitee')) + array_sum(array_column($listes, 'sieste'));
                        $bilans[$antenne->getId()] = $listes;
                        $totaux[$antenne->getId()] = $total;                     
                    }
                    $data = $this->renderView('admin/bilan/bilanjournalieradmin.html.twig', [ 
                        "bilans" => $bilans,
                        "debut" => $debut,
                        "fin" => $fin,
                        "antennes" => $antennes,
                        "today" => $today,
                        "totaux" => $totaux,
                    ]);
                }else{
                    $bilans = $this->em->getRepository(Allocation::class)->bilanPeriodique($antenne->getId(), $debut, $fin, $today);
                    $data = $this->renderView('admin/bilan/bilanjournalier.html.twig', [ 
                        "bilans" => $bilans,
                        "debut" => $debut,
                        "fin" => $fin,
                        "today" => $today,
                        "antenne" => $antenne
                    ]);
                }                
                $this->successResponse("Bilan des entrées financières ", $link, $data);
            }else{
               return $this->redirectToRoute('login');
            }  
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }

    /**
     * @Route("/printbilanjournaliergenerale", name="printbilanjournaliergenerale")
     */
    public function printbilanjournaliergenerale(Request $request){
        $link="bilanjournalier";
        $user = $this->getUser();
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $today = $request->get('today');
        $bilans = array();
        $totaux = array();
        try {
            if($user){
                $antenne = $user->getAntene();
                if($user->getIsadmin()){
                    $antennes = $this->em->getRepository(Entene::class)->findAll();
                    foreach ($antennes as $antenne) {
                        $listes = $this->em->getRepository(Allocation::class)->bilanPeriodique($antenne->getId(), $debut, $fin, $today);
                        $total = array_sum(array_column($listes, 'nuitee')) + array_sum(array_column($listes, 'sieste'));
                        $bilans[$antenne->getId()] = $listes;
                        $totaux[$antenne->getId()] = $total;                     
                    }
                    $template = $this->renderView('admin/print/printbilanjournalieradmin.pdf.twig', [ 
                        "bilans" => $bilans,
                        "debut" => $debut,
                        "fin" => $fin,
                        "antennes" => $antennes,
                        "today" => $today,
                        "totaux" => $totaux,
                        "antene" => $antenne
                    ]);

                }else{
                    $bilans = $this->em->getRepository(Allocation::class)->bilanPeriodique($antenne->getId(), $debut, $fin, $today);
                    $template = $this->renderView('admin/print/printbilanjournalier.pdf.twig', [ 
                        "bilans" => $bilans,
                        "debut" => $debut,
                        "fin" => $fin,
                        "today" => $today,
                        "antene" => $antenne
                    ]);
                }                
                return $this->returnPDFResponseFromHTML($template, "bilan des entrées financières",); 
            }else{
               return $this->redirectToRoute('login');
            }  
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }        
    }

     /**
     * @Route("/monbilanjournalier", name="mon-bilanjournalier", methods={"GET"})
    */
    public function monbilanjournalier(Request $request)
    {
        $link="monbilanjournalier";
        $user = $this->getUser();
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $today = $request->get('today');
        $bilans = array();
        try {
            if($user){
                if($user->getIsadmin()){       
                    $bilans = $this->em->getRepository(Allocation::class)->monBilan($this->getAllAntennes(), $user, $debut, $fin, $today);                     
                }else{
                    $bilans = $this->em->getRepository(Allocation::class)->monBilan([$user->getAntene()], $user, $debut, $fin, $today);
                }     
                    $data = $this->renderView('admin/bilan/monbilanjournalier.html.twig', [ 
                        "bilans" => $bilans,
                        "debut" => $debut,
                        "fin" => $fin,
                        "today" => $today,
                        "antenne" => $user->getAntene()
                    ]);        
                    $this->successResponse("Bilan des mes entrées financières ", $link, $data);
            }else{
               return $this->redirectToRoute('login');
            }  
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }

    /**
     * @Route("/printmonbilanjournalierofuser", name="printmonbilanjournalierofuser", methods={"GET"})
    */
    public function printmonbilanjournalierofuser(Request $request)
    {
        $link="printbilancurrentdayofantene";
        $user = $this->getUser();
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $today = $request->get('today');
        $bilans = array();
        try {
            if($user){
                if($user->getIsadmin()){       
                    $bilans = $this->em->getRepository(Allocation::class)->monBilan($this->getAllAntennes(), $user, $debut, $fin, $today);
                    }else{
                        $bilans = $this->em->getRepository(Allocation::class)->monBilan([$user->getAntene()], $user, $debut, $fin, $today);
                    }     
                    $template = $this->renderView('admin/print/printmonbilanjournalier.pdf.twig', [ 
                        "bilans" => $bilans,
                        "debut" => $debut,
                        "fin" => $fin,
                        "today" => $today,
                        "antene" => $user->getAntene()
                    ]);  
                    return $this->returnPDFResponseFromHTML($template, "Mon bilan journalier"); 
            }else{
              return $this->redirectToRoute('login');
            }  
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
            return $this->json($this->result);
        }         
    }

    /**
     * @Route("/bilancurrentdayofantene", name="bilancurrentday-ofantene", methods={"GET"})
    */
    public function bilancurrentdayofantene(Request $request){
        $iduser = intval($request->get('iduser'));
        $idantenne = intval($request->get('idantenne'));
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $today = $request->get('today');
        $retour = $request->get('retour');
        $details = array();
        $link= $this->generateUrl($retour, ['iduser'=>$iduser, 'idantenne'=>$idantenne, 'today'=>$today, 'debut'=>$debut, 'fin'=>$fin]);
        try {
            $user = $this->getUser();
            if($user){
                $antenne = $user->getAntene();
                $userEntree = $this->em->getRepository(User::class)->find($iduser);
                if($userEntree){
                    $details = $this->em->getRepository(Allocation::class)->detailsEntrees($idantenne, $iduser, $today, $debut, $fin);
                    $data = $this->renderView('admin/bilan/bilanjournalierdetails.html.twig', [
                        'antenne' => $antenne,
                        'details' => $details,
                        'today' => $today,
                        'debut' => $debut,
                        'fin' => $fin,
                        'user' => $userEntree,
                        'link' => $link
                    ]);
                    $this->successResponse("Détails des entrées financières ", $link, $data);
                }else{
                    $this->log("Utilisateur introuvable", $link);
                }
                
            }else{
                return $this->redirectToRoute('login');
            }
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }

    /**
     * @Route("/printbilancurrentdayofantene", name="printbilancurrentday-ofantene", methods={"GET"})
    */
    public function printbilancurrentdayofantene(Request $request){
        $link="allocation";
        $idantene = $request->get('id');
        $vurrenttime = new DateTime();
        $antene = $this->em->getRepository(Entene::class)->find($idantene);
        $allocationday = $this->em->getRepository(Allocation::class)->getallocationantnebydate($idantene,$vurrenttime);
        $template = $this->renderView('admin/print/printtbilanjournalierantene.pdf.twig', [
            'anteneforbilan'=>$antene,
            'allocations'=>$allocationday,
            "antene"=>$user=$this->getUser()->getAntene(),
        ]);
    
        // dd($this->result);
        return $this->returnPDFResponseFromHTML($template, "bilan journalier des antenes", "L"); 

    }

   

    
    
    /**
     * @Route("/journal", name="journal-index", methods={"GET"})
    */
    public function journal(Request $request){
        
        $link="journal";
        try {
                $antenneID = $request->get("antenne") ? intval($request->get("antenne")) : null;
                $antenne = null;
                $jour = $request->get('jour'); 
                if($jour){
                  $jour = new DateTime($jour);
                }else{
                  $jour = new Datetime();
                }              
                if($antenneID){
                    $antenne = $this->em->getRepository(Entene::class)->find($antenneID);
                    $entrees = $this->em->getRepository(Allocation::class)->findBy(['createat'=>$jour, "antene"=>$antenne]);
                    $sorties = $this->em->getRepository(SortieFinanciere::class)->findBy(['createdAt'=> $jour, "antenne"=>$antenne]);
                }else{
                    $entrees = $this->em->getRepository(Allocation::class)->findBy(['createat'=>$jour]);
                    $sorties = $this->em->getRepository(SortieFinanciere::class)->findBy(['createdAt'=> $jour]);
                }
                
                $donnees = array();
                for ($i=0; $i < max(count($entrees), count($sorties)); $i++) { 
                    $donnees[$i]["code1"] = array_key_exists($i, $entrees) ? $entrees[$i]->getCompte()->getCode() : "-";
                    $donnees[$i]["intitule1"] = array_key_exists($i, $entrees) ? $entrees[$i]->getCompte()->getIntitule() : "-";
                    $donnees[$i]["montant1"] = array_key_exists($i, $entrees) ? $entrees[$i]->getMontant() : 0;
                    $donnees[$i]["code2"] = array_key_exists($i, $sorties) ? $sorties[$i]->getCompte()->getCode() : "-";
                    $donnees[$i]["intitule2"] = array_key_exists($i, $sorties) ? $sorties[$i]->getCompte()->getIntitule() : "-";
                    $donnees[$i]["montant2"] = array_key_exists($i, $sorties) ? $sorties[$i]->getMontant() : 0;
                }
             //  dd($donnees);
                $antennes = $this->em->getRepository(Entene::class)->findAll();              
                $data = $this->renderView('admin/bilan/journal.html.twig', 
                [
                    "antennes"=>$antennes,
                    "jour"=>$jour,
                    "donnees"=>$donnees,
                    "antenne"=>$antenne
                ]);
                $this->successResponse("Journal comptable affiché", $link, $data);
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }    
    /**
     * @Route("/printjournal", name="print-journal", methods={"GET"})
    */
    public function printJournal(Request $request){
        
        $link="journal";
        $antenneID = $request->get("antenne") ? intval($request->get("antenne")) : null;
        $antenne = null;
        try {
            $jour = $request->get('jour'); 
            if($jour){
                $jour = new DateTime($jour);
            }else{
                $jour = new Datetime();
            }              
            if($antenneID){
                $antenne = $this->em->getRepository(Entene::class)->find($antenneID);
                $entrees = $this->em->getRepository(Allocation::class)->findBy(['createat'=>$jour, "antene"=>$antenne]);
                $sorties = $this->em->getRepository(SortieFinanciere::class)->findBy(['createdAt'=> $jour, "antenne"=>$antenne]);
            }else{
                $entrees = $this->em->getRepository(Allocation::class)->findBy(['createat'=>$jour]);
                $sorties = $this->em->getRepository(SortieFinanciere::class)->findBy(['createdAt'=> $jour]);
            }
            $donnees = array();
            for ($i=0; $i < max(count($entrees), count($sorties)); $i++) { 
                $donnees[$i]["code1"] = array_key_exists($i, $entrees) ? $entrees[$i]->getCompte()->getCode() : "-";
                $donnees[$i]["intitule1"] = array_key_exists($i, $entrees) ? $entrees[$i]->getCompte()->getIntitule() : "-";
                $donnees[$i]["montant1"] = array_key_exists($i, $entrees) ? $entrees[$i]->getMontant() : 0;
                $donnees[$i]["code2"] = array_key_exists($i, $sorties) ? $sorties[$i]->getCompte()->getCode() : "-";
                $donnees[$i]["intitule2"] = array_key_exists($i, $sorties) ? $sorties[$i]->getCompte()->getIntitule() : "-";
                $donnees[$i]["montant2"] = array_key_exists($i, $sorties) ? $sorties[$i]->getMontant() : 0;
            }
                $template = $this->renderView('admin/print/printJournal.pdf.twig', [
                    'donnees'=>$donnees,
                    "antene"=>$this->getUser()->getAntene(),
                    "antenne"=>$antenne,
                    "jour"=>$jour
                ]);             
               
                return $this->returnPDFResponseFromHTML($template, "Journal comptable"); 
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }
}
