<?php

namespace App\Controller;

use App\Entity\Allocation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Chambre;
use App\Entity\Role;
use App\Entity\Parametre;
use App\Entity\Tarif;
use App\Entity\Reservation;
use \DateInterval;
use App\Entity\Typechambre;
use App\Entity\Entene;
use App\Entity\User;
use App\Entity\Transaction;
use \Datetime;
use DoctrineExtensions\Query\Mysql\Now;

class AllocationController extends DefaultController
{

    /**
     * @Route("/indexallocation", name="index-allocation", methods={"GET"})
     */
    public function indexallocation(Request $request){

        $now = new \DateTime(date('Y-m-d H:i:s'));
        $link = "getallchambrelibre";
        $type = "SIESTE";
        $user = $this->getUser();
        if($user){
            try {
                // if($user->getIsadmin()){
                //     $allocations = $this->em->getRepository(Allocation::class)->getChambreLibres($now, $type);
                //     $page = "admin/allocation/indexsieste.html.twig";
                //     $chambres = $this->em->getRepository(Chambre::class)->findAll();
                //     $types = $this->em->getRepository(Typechambre::class)->findAll();
                // }else{
                    $antenne = $user->getAntene();
                    $allocations = $this->em->getRepository(Allocation::class)->getChambreLibres($now, null, $antenne->getId());                    
                    $chambreIds = array();
                    //dd($allocations);
                    foreach ($allocations as $allocation) {
                        array_push($chambreIds, $allocation->getChambre()->getId());
                    }
                    $clients = $this->em->getRepository(User::class)->findBy(['type'=>"CLIENT", 'antene'=>$antenne]);
                    $page = "admin/allocation/indexsieste.html.twig";
                   // $chambres = $this->em->getRepository(Chambre::class)->findBy(['entene'=>$antenne]);
                    $types = $this->em->getRepository(Typechambre::class)->findBy(['antene'=>$antenne]);
               // }                
                $data = $this->renderView($page, [
                    'allocations'=>$allocations, 'types'=>$types, 'chambreIds'=>$chambreIds, 'clients'=>$clients
                ]);
                $this->successResponse("Chambres libres", $link, $data);
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{            
           return $this->redirect('/login');
        }
        
    }
    
    /**
     * @Route("/allouer/{id}", name="allouer", methods={"GET"})
     */
    public function allouer($id)
    {
        $link="allocation";

        $user = $this->getUser();
        try {
            if($user){
               
                $reservations = $this->em->getRepository(Reservation::class)->find($id);
                $typchambres = $this->em->getRepository(Typechambre::class)->findAll();
                $chambres = $this->em->getRepository(Chambre::class)->findAll();
                $tarif = $this->em->getRepository(Tarif::class)->findAll();
                $clients = $this->em->getRepository(User::class)->findBy(['type' => $reservations]);
                
                // $clients = $this->em->getRepository(User::class)->find($id);
                //dd($clients);
                $data = $this->renderView('admin/reservation/alouerExterne.html.twig', [
                    "typchambres" => $typchambres,
                    "chambres" => $chambres,
                    "tarifs" => $tarif,
                    "reservations" => $reservations,
                    "clients" => $clients
                ]);
                $this->successResponse("Liste des allocations ", $link, $data);
            }else{
                return $this->redirect('/login');
            }
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }


    /**
     * @Route("/siesteadd", name="sieste-add", methods={"POST"})
     */
    public function siesteadd(Request $request)
    {       
        $link="allocation";
        $user = $this->getUser();
        if($user){
            $compte = current($this->em->getRepository(Parametre::class)->findAll())->getCompteHebergement();
            if(!is_null($compte)){
                try {
                    $client =  $this->em->getRepository(User::class)->findBy([
                        'username' => "divers"
                    ])[0];
                    $datedeb = new \DateTime('now');
                    
                    ///calcul duree du sejour
                    $quantite=$request->get("qte");
                    $packsejour=$request->get("sejourclient");
                    $tarif = $this->em->getRepository(Tarif::class)->find($packsejour);
                    $nombreheure=$tarif->getDuree()*intval($quantite);

                    $montant=$tarif->getPrix()*intval($quantite);

                    $timefin = new \DateTime('now');
                    
                    ////cacul du temp de fin 
                    $timefin->add(new DateInterval('PT'.$nombreheure.'H'));
                    /////fin calcule temps de fin 
        
                    $cham = $request->get("chambre");
                    $chambre = $this->em->getRepository(Chambre::class)->find($cham);
                    //dd($chambre);

                    //Create user if not exit
                    $allocation = new Allocation();
        
                    $allocation->setDatedebut($datedeb);
                    $allocation->setDatefin($timefin);
        
        
                    //$allocation->setMontant($debiter);
                    $allocation->setMontant($montant);
                    $allocation->setOperateur($this->getUser());
                    $allocation->setAntene($this->getUser()->getAntene());
                    $allocation->setReduction(0);
                    $allocation->setOccupant($client);
                    $allocation->setChambre($chambre);
                    $allocation->setCompte($compte);
                    $allocation->setType($tarif->getType());
                    $allocation->setCreateat(new \DateTime('now'));
                    
                    $this->em->persist($allocation);
                    $this->em->flush();

                    $this->setlog("AJOUTER","l'utilisateur ".$this->getUser()->getUsername().
                        " a ajouter une allocation ".$chambre->getNumero(),"ALLOCATION",$chambre->getId());
        
                    $result = array("success"=>true,"id"=>$allocation->getId());
                    //dd($result);
                    return new JsonResponse($result);
                    
                }catch (\Exception $ex) {
                    $result = array("success"=>false,"id"=>$ex->getMessage());
                    return new JsonResponse($result);
                } 
            }else{
                $this->log("Aucun compte d'op??ration configur?? dans les param??tres.", $link);
            }       
        }else{
            return $this->redirect('/login');
        }
    }


    /**
     * @Route("/allocationadd", name="allocation-add", methods={"POST"})
     */
    public function allocationadd(Request $request){
        
        $result = array();
        $link = "allocation";
        $check_nouveau = $request->get('nouveau_client');
        $userg = $this->getUser();
        $client = new User();
        if($userg){
           // $id = $request->get("id");
            $chambreID = intval($request->get("chambreID"));
            $hebergement = $request->get("hebergement");
            $occurence = intval($request->get("occurence"));
            $prix = floatval($request->get('prix'));
            $versement = floatval($request->get('versement'));
            $strDate = $request->get('jour').$request->get('heure').":00";
            $datedebut = new \Datetime($strDate);
            $accompte=$request->get("accompte");            
                try {
                    $compte = current($this->em->getRepository(Parametre::class)->findAll())->getCompteHebergement();
                    if(!is_null($compte)){
                        $chambre = $this->em->getRepository(Chambre::class)->find($chambreID);
                        if($chambre){
                            if($hebergement){
                                if($occurence != 0){
                                    if ($versement != 0) {
                                        if ($prix != 0) {
                                            if($datedebut){                                              
                                                if($check_nouveau == 'on'){                                                
                                                    $nom =  $request->get('nom');
                                                    $prenom =  $request->get('prenom');
                                                    $sexe =  $request->get('sexe');
                                                    $cni =  $request->get('cni');
                                                    if($nom){
                                                        $client->setNom($nom);
                                                        $client->setPrenom($prenom);
                                                        $client->setType("CLIENT");
                                                        $client->setSexe($sexe);
                                                        $client->setCni($cni);
                                                        $client->setEmail($nom."".$prenom."@hotelapp.com");
                        
                                                        $client->setCni($cni);
                                                        $client->setUsername(trim($nom."".uniqid()));
                                                        $client->setPassword($this->passwordEncoder->encodePassword($client,"12345@abc"));
                                                        $client->setIsadmin(0);
                                                        $client->setAntene($userg->getAntene());
                                                        $this->em->persist($client);
                                                    }else{
                                                        // $this->log("Le nom est obligatoire.");
                                                        $message = "Le nom est obligatoire.";
                                                        $result = array("success"=>false,"id"=>-1, "message"=>$message);  
                                                        return $this->json($result);      
                                                    }
                                                }else{
                                                    $clientId = intval($request->get("client"));
                                                    $client = $this->em->getRepository(User::class)->find($clientId);
                                                    if(is_null($client)){
                                                        $message = "Client introuvable.";
                                                        $result = array("success"=>false,"id"=>-1, "message"=>$message);  
                                                        return $this->json($result);      
                                                    }
                                                }                             
                                                if($hebergement === "SIESTE"){
                                                    $datefin = new \DateTime(date("Y-m-d H:i:s", strtotime($strDate." +".$occurence." hour")));
                                                }else{
                                                    $datefin = new \DateTime(date("Y-m-d H:i:s", strtotime($strDate." +".$occurence." day")));
                                                }           
                                              //  dd($datedebut, $datefin);  
                                                if($accompte){
                                                    $solde = $client->getSolde("solde");                                                 
                                                    if($solde >= $versement){                                                
                                                        $debiter=($solde-$versement);
                                                        $solde = $client->getSolde("solde");
                                                        $client->setSolde($debiter);
                                                        $this->em->persist($client);    

                                                        $transact = new Transaction();
                                                        $transact->setMontant($versement);
                                                        $transact->setClient($client);                                                    
                                                        $transact->setType("DEBIT");
                                                        $transact->setCreatedat(new \DateTime('now'));
                                                        $transact->setCreatedby($this->getUser());
                                                        $this->em->persist($transact);
                                                    } else{
                                                        $this->addFlash('danger','solde insuffisant,veuiller Recharger votre compte.');
                                                    }       
                                                }         
                                                $allocation = new Allocation();    
                                                $allocation->setDatedebut($datedebut);
                                                $allocation->setDatefin($datefin);
                                                $allocation->setMontant($versement);
                                                $allocation->setOperateur($this->getUser());
                                                $allocation->setAntene($this->getUser()->getAntene());
                                                $allocation->setReduction($prix-$versement);
                                                $allocation->setOccupant($client);
                                                $allocation->setChambre($chambre);
                                                $allocation->setCompte($compte);
                                                $allocation->setType($hebergement);
                                                $allocation->setCreateat(new \DateTime('now'));
                                                $this->em->persist($allocation);
                                                $this->em->flush();
                                                $this->setlog("AJOUTER","l'utilisateur ".$this->getUser()->getUsername().
                                                    " a ajout?? une allocation ".$chambre->getNumero(),"ALLOCATION",$chambre->getId());
                                                $result = array("success"=>true,"id"=>$allocation->getId());
                                                return new JsonResponse($result);
                                            }else{
                                                $message = "Date de debut obligatoire obligatoire";
                                                $result = array("success"=>false,"id"=>-1, "message"=>$message);        
                                                return new JsonResponse($result);
                                            }
                                        } else {
                                            // $this->log("Le prix obligatoire"); 
                                            $message = "Le prix obligatoire";
                                            $result = array("success"=>false,"id"=>-1, "message"=>$message);        
                            return new JsonResponse($result);
                                            
                                        }                                    
                                    } else {
                                        // $this->log("Versement obligatoire"); 
                                        $message = "Versement obligatoire";
                                        $result = array("success"=>false,"id"=>-1, "message"=>$message);        
                            return new JsonResponse($result);
                                        
                                    }                                
                                }else{
                                    // $this->log("Occurence obligatoire"); 
                                    $message = "Occurence obligatoire";
                                    $result = array("success"=>false,"id"=>-1, "message"=>$message);        
                            return new JsonResponse($result);
                                }
                            }else{
                                // $this->log("H??bergement obligatoire"); 
                                $message = "H??bergement obligatoire";
                                $result = array("success"=>false,"id"=>-1, "message"=>$message);        
                            return new JsonResponse($result);
                                
                            }
                        }else{
                            // $this->log("Chambre obligatoire");       
                            $message = "Chambre obligatoire";   
                            $result = array("success"=>false,"id"=>-1, "message"=>$message);        
                                 
                        }
                    }else{
                        // $this->log("Aucun compte d'op??ration configur?? dans les param??tres.", $link);
                        $message = "Aucun compte d'op??ration configur?? dans les param??tres.";
                    }     
                }catch (\Exception $ex) {
                    $result = array("success"=>false,"id"=>-1, "message"=>$ex->getMessage());
                    return new JsonResponse($result);
                } 
              
        }else{
            return $this->redirect('/login');
        }
        return new JsonResponse($result);
    }

    /**
     * @Route("/validereservation", name="valide-reservation", methods={"POST"})
     */
    public function validereservation(Request $request){
        $userg = $this->getUser();
        if($userg){
            $id = $request->get("id");
            $link="allocation";
            $radio=$request->get("radio");
            $compte = current($this->em->getRepository(Parametre::class)->findAll())->getCompteHebergement();
            if(!is_null($compte)){
                try {

                    $oldornew =  $request->get('noveauclientckeck');

                    if($oldornew=="on"){ 
                        
                            $nom =  $request->get('nom');
                            $prenom =  $request->get('prenom');
                            $datenais=  $request->get('datenaisance');
                            $datenaisance = new \DateTime($datenais);
                            $sex =  $request->get('sex');
                            $cni =  $request->get('cni');
                            $lieunaisance =  $request->get('lieunaisance');
                            $etatcivil =  $request->get('etatcivil');
                            $profession =  $request->get('profession');
                            $nationalite =  $request->get('nationalite');
                            $phone =  $request->get('phone');
                            $adresse =  $request->get('adresse');
                            $photo = $request->files->get("photo");
                            $user = new User();
                            $user->setNom($nom);
                            $user->setPrenom($prenom);
                            $user->setDatenaisance($datenaisance);
                            $user->setSexe($sex);
                            
                            if(empty($request->get("email"))){
                                $user->setEmail($nom."".$prenom."@hotelapp.com");
                            }else{
                                $user->setEmail($request->get("email"));
                            }
                            
                            $user->setCni($cni);
                            $user->setUsername(trim($nom."".$prenom));
                            $user->setPassword($this->passwordEncoder->encodePassword($user,"12345@abc"));
                            $user->setLieunaisance($lieunaisance);
                            $user->setEtatcivil($etatcivil);
                            
                            $user->setProfession($profession);
                            $user->setNationalite($nationalite);
                            $user->setPhone($phone);

                            $user->setAdresse($adresse);
                            $user->setType("CLIENT");
                            $this->em->persist($user);
                            $this->em->flush();
                            $client=$user;
                            

                    }else{
                        $clientid =  $request->get('client');
                        $client = $this->em->getRepository(User::class)->find($clientid);
                        
                    
                    
                    }

                    //changee etat when allouer
                    $reservations = $this->em->getRepository(Reservation::class)->find($id);
                    $etat = $reservations->getEtat("etat");
                    //dd();
                    if($etat=="Non-trait??"){ 
                        $reservations->setEtat("Trait??");
                    
                        
                    }else{
                        $reservations->setEtat("Trait??");
                    
                    }
                    //dd($reservations);
                    $this->em->persist($reservations);
                    $this->em->flush();
                


                    $dateat = $request->get("arriver");
                    $datedeb = new \DateTime($dateat);
                    
                    ///calcul duree du sejour
                    $quantite=$request->get("qte");
                    $packsejour=$request->get("sejourclient");
                    $tarif = $this->em->getRepository(Tarif::class)->find($packsejour);
                    $nombreheure=$tarif->getDuree()*intval($quantite);

                    ///allocationn via debit
                    $solde = $client->getSolde("solde");
                    $montant=$tarif->getPrix()*intval($quantite);

                    $radio=$request->get("radio");
                    
                
                        
                if($radio=="acompte")
                {

                        if($solde>=$montant)
                        {
                        
                            $debiter=($solde-$montant);
                            $clientid =  $request->get('client');
                            $client = $this->em->getRepository(User::class)->find($clientid);
                            $solde = $client->getSolde("solde");
                            $client->setSolde($debiter);
                            $this->em->persist($client);



                            $transact = new Transaction();
                            $transact->setMontant($montant);
                            $transact->setClient($client);
                            
                            $transact->setType("DEBIT");
                            $transact->setCreatedat(new \DateTime('now'));
                            $transact->setCreatedby($this->getUser());
                        //dd($transact);
                            $this->em->persist($transact);
                        
                        }else
                        {
                        
                            $this ->addFlash('danger','solde insuffisante,veiller Recharger votre compte svp');
                            return $this->redirectToRoute('index-allocation',[]);
                        
                        }
                    
                    }

                    ///fin calcul durrer  du sejour 
        
                    $timeat = $request->get("arriver");
                    $timefin = new \DateTime($timeat);
                    
                    ////cacul du temp de fin 
                    $timefin->add(new DateInterval('PT'.$nombreheure.'H'));
                    /////fin calcule temps de fin 
        
                    $cham = $request->get("chambre");
                    $chambre = $this->em->getRepository(Chambre::class)->find($cham);
                
                    //Create user if not exit
                    $user = new User();
                    if(is_null($client)){
                        $nom =  $request->get('nom');
                        $prenom =  $request->get('prenom');
                        $sex =  $request->get('sex');
                        $cni =  $request->get('cni');
                        
                        //$phone =  $request->get('phone');

                        $user->setNom($nom);
                        $user->setPrenom($prenom);
                        $user->setSexe($sex);
                        $user->setEmail($nom."".$prenom."@hotelapp.com");
                        
                        $user->setCni($cni);
                        $user->setUsername(trim($nom."".$prenom));
                        $user->setPassword($this->passwordEncoder->encodePassword($user,"12345@abc"));
                        //$user->setPhone($phone);
                        $user->setType("CLIENT");
                        

                        $this->em->persist($user);
                        $this->em->flush();
                        $client = $user;
                    }
                    $allocation = new Allocation();
        
                    $allocation->setDatedebut($datedeb);
                    $allocation->setDatefin($timefin);
        
                    $allocation->setMontant($montant);
                    $allocation->setOperateur($this->getUser());
                    $allocation->setAntene($this->getUser()->getAntene());
                    $allocation->setReduction(0);
                    $allocation->setOccupant($client);
                    $allocation->setChambre($chambre);
                    $allocation->setCompte($compte);
                    $allocation->setType($tarif->getType());
                    $allocation->setCreateat(new \DateTime('now'));
                    $this->em->persist($allocation);
                    $this->em->flush();
                    $this->setlog("AJOUTER","l'utilisateur ".$this->getUser()->getUsername().
                        " a ajouter une allocation ".$chambre->getNumero(),"ALLOCATION",$chambre->getId());
        
                    $result = array("success"=>true,"id"=>$allocation->getId());
                    return new JsonResponse($result);
                    
                }catch (\Exception $ex) {
                    $result = array("success"=>false,"id"=>$ex->getMessage());
                    return new JsonResponse($result);
                } 
            }else{
                $this->log("Aucun compte d'op??ration configur?? dans les param??tres.", $link);
            }
        }else{
            return $this->redirect('/login');
        }      
        
    }





    
    public function checkdisponibility($heurearriverclient,$heurefinclient,$heuredebut,$heurefin){
        $userg = $this->getUser();
        if($userg){
            //dd(gettype($heurearriverclient));
            // $heurearriverclientok = new \DateTime($heurearriverclient);
            // $heurefinclientok = new \DateTime($heurefinclient);
            // if($heurearriverclientok<=$heuredebut){
            //     $cars = array("inferieux1"=>$heurearriverclientok,"superieux1"=>$heuredebut); 
            //     dd($cars);
            // }else{
            //     $cars = array("inferieux2"=>$heuredebut,"superieux2"=>$heurearriverclientok);
            //     dd($cars);
            // }
            //algorithme de test de compatibilite de occupation
            // 2 evernement a b 
            //f(a) <= d(b) ou f(b)<=d(a)
        //     $okolo = array(
        //     "heurearriverclient"=>$heurearriverclient
        //     ,"heurefinclient"=>$heurefinclient
        //     ,"heuredebut"=>$heuredebut
        //     ,"heurefin"=>$heurefin
        // );
        //     dd($okolo);
            $compatible=true;
            if(($heurefinclient<=$heuredebut) || ($heurefin<=$heurearriverclient)){
                $compatible=true;
            }else{
                $compatible=false;
            }
            return $compatible;

        }else{
            return $this->redirect('/login');
        }
    }

    /**
     * @Route("/getalltarif", name="getalltarif", methods={"GET"})
     */
    public function getalltarif(Request $request){
        $userg = $this->getUser();
        if($userg){
            $id = $request->get("id");
        
            $tarifs = $this->em->getRepository(Tarif::class)->findBy([
                'typechambre' => $id,
                'type' =>'NUITEE'
            ]);
            $items = array(); 
            foreach($tarifs as $tarif) {
            $curentitems = array("id"=>$tarif->getId(),"duree"=>$tarif->getDuree(), "prix"=>$tarif->getPrix(), "nom"=>$tarif->getNom());
            $items[] = $curentitems;
            }
            return new JsonResponse($items);
        }else{
            return $this->redirect('/login');
        }
    }

    /**
     * @Route("/getsiestetarif", name="getsiestetarif", methods={"GET"})
     */
    public function getsiestetarif(Request $request){
        $userg = $this->getUser();
        if($userg){
            $id = $request->get("id");
        
            $tarifs = $this->em->getRepository(Tarif::class)->findBy([
                'typechambre' => $id,
                'type' =>'SIESTE'
            ]);
            $items = array(); 
            foreach($tarifs as $tarif) {
            $curentitems = array("id"=>$tarif->getId(),"duree"=>$tarif->getDuree(), "prix"=>$tarif->getPrix(), "nom"=>$tarif->getNom());
            $items[] = $curentitems;
            }
            return new JsonResponse($items);
        }else{
            return $this->redirect('/login');
        }
    }

	/**
     * @Route("/getcanpay", name="getcanpay", methods={"GET"})
     */
    public function getcanpay(Request $request){
        $userg = $this->getUser();
        if($userg){
            $id = $request->get("id");
            $client = $this->em->getRepository(User::class)->find($id);
            $solde = $client->getSolde(); 
            
            return new JsonResponse($solde);
        }else{
            return $this->redirect('/login');
        }
    }
	
    /**
     * @Route("/getallchambrelibre", name="getallchambrelibre", methods={"POST"})
     */
    public function getallchambrelibre(Request $request){
        $now = new \DateTime(date('Y-m-d H:i:s'));
        $link = "getallchambrelibre";
        $type = "SIESTE";
        $user = $this->getUser();
        if($user){
            try {
                if($user->getIsadmin()){
                    $chambres = $this->em->getRepository(Allocation::class)->getallchambrelibre($now, $type);
                    $page = "admin/allocation/indexsieste.html";
                }else{
                    $chambres = $this->em->getRepository(Allocation::class)->getallchambrelibre($now, $type, $user->getAntene()->getId);                    
                    $page = "admin/allocation/index.html";
                }                
                dd($chambres);
                $data = $this->renderView($page, ['chambres'=>$chambres]);
                $this->successResponse("Chambres libres", $link, $data);
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirect('/login');
        }
        

    }
    /*
    public function getallchambrelibre(Request $request){
        $userg = $this->getUser();
        if($userg){

            $datearriver=$request->get("datearrivee");
            $heurefin=$request->get("heuredarrivee");

            ///new variable  
            $heurearriverclient=$request->get("datearrivee");
            $heuredepartclient=$request->get("datearrivee");
            $heurearriverclient=new \DateTime($heurearriverclient);
            $heuredepartclient=new \DateTime($heuredepartclient);
            $packsejour=$request->get("duree");
            $quantite=$request->get("quantite");
            $tarif = $this->em->getRepository(Tarif::class)->find($packsejour);
            $nombreheure=$tarif->getDuree()*intval($quantite);
            //$heuredepartclient=$heurearriverclient;
            
            $heuredepartclient->add(new DateInterval('PT'.$nombreheure.'H'));
            //end new variable
            //dd($heurearriverclient);
            $chambres = $this->em->getRepository(Chambre::class)->findBy(
                ['type' => $request->get("typechambre")]);
            

            //////////temp
                $items = array();
                foreach($chambres as $chambre) {
                
                }
                //return new JsonResponse($items);
            /////////
            foreach ($chambres as $chambre) {
                //($chambreid,$heuredebut,$heurefin)
            //dd($heurearriverclient);
                $resultcheckchambre=$this->checkifroomfree($chambre->getId(),$heurearriverclient,$heuredepartclient);
                
                if($resultcheckchambre){
                    $curentitems = array("id"=>$chambre->getId(),"numero"=>$chambre->getNumero());
                    $items[] = $curentitems;
                }
            
            }
            return new JsonResponse($items);
        }else{
            return $this->redirect('/login');
        }
        
        
    }*/

    public function checkifroomfree($chambreid,$heuredebut,$heurefin){
        $userg = $this->getUser();
        if($userg){

            //$datejour=new \DateTime($jour);
            $plannings = $this->em->getRepository(Allocation::class)->findBy(
                ['chambre' => $chambreid]);
            //,'datedebut' => $datejour
            // dd($heurefin);
            $dispo=true;
            foreach ($plannings as $planningitem) {
                $debutplaning=$planningitem->getdateDebut();
                $finplaning=$planningitem->getDatefin();

                $check=$this->checkdisponibility($heuredebut,$heurefin,$debutplaning,$finplaning);
                if(!$check){
                    $dispo=false;
                }
            } 
            return  $dispo;
        }else{
            return $this->redirect('/login');
        }
    }

    /**
     * @Route("/printrecuallocation", name="print-recu", methods={"GET"})
     */
    public function printrecus(Request $request)
    {
        $userg = $this->getUser();
        if($userg){

            $idallocation=$request->get("id");
            $allocation = $this->em->getRepository(Allocation::class)->find($idallocation);
            
            return $this->printRecu($allocation);

        }else{
            return $this->redirect('/login');
        }
    }
	
	/**
     * @Route("/listeallocation", name="liste-allocation", methods={"GET"})
     */
    public function listeallocation()
    {
        $link="listallocation";
        $user = $this->getUser();
        if($user){
            try {            
                if($user->getIsadmin()){
                    $typchambres = $this->em->getRepository(Typechambre::class)->findAll();
                    $allocations = $this->em->getRepository(Allocation::class)->findAll();
                    //dd($allocations);
                    $chambres = $this->em->getRepository(Chambre::class)->findAll();
                    $tarif = $this->em->getRepository(Tarif::class)->findAll();
                    
                    $clients = $this->em->getRepository(User::class)->findBy(
                        ['type' => "CLIENT"]);
                
                }else{
                    $typchambres = $this->em->getRepository(Typechambre::class)->findAll();
                    $allocations = $this->em->getRepository(Allocation::class)->findBy([
                        "antene"=>$user->getAntene()
                    ]);
                    //dd($allocations);
                    //dd($allocations);
                    $chambres = $this->em->getRepository(Chambre::class)->findAll();
                    $tarif = $this->em->getRepository(Tarif::class)->findAll();
                    
                    $clients = $this->em->getRepository(User::class)->findBy(
                        ['type' => "CLIENT"]);
                    //dd($clients);
                }
                $data = $this->renderView('admin/allocation/liste.html.twig', [
                    "typchambres" => $typchambres,
                    "chambres" => $chambres,
                    "tarifs" => $tarif,
                    "allocations" => $allocations,
                    "clients" => $clients
                ]);
                $this->successResponse("Liste des allocations ", $link, $data);

            }catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            // dd($this->result);
            return $this->json($this->result);
        }else{
            return $this->redirect('/login');
        }
    }

    /**
     * @Route("/printallocation", name="print-allocation")
     */
    public function printallocation(){
        $userg = $this->getUser();
        if($userg){
            $link="allocation";
            $antene = $this->getUser()->getAntene();
            $allocations = $this->em->getRepository(Allocation::class)->findAll();
            //dd($allocations);
            $template = $this->renderView('admin/print/printallocation.pdf.twig', [
                "allocation" => $allocations,
                "antene" => $antene
            ]);
            
            // dd($this->result);
            return $this->returnPDFResponseFromHTML($template, "Liste des allocations"); 
        }else{
            return $this->redirect('/login');
        }
    }
	
	
	 /**
     * @Route("/allocationreser", name="allocation-reser", methods={"POST"})
     */
    public function allocationreser(Request $request)
    {
        $link="allocation";
        $userg = $this->getUser();
        if($userg){
            $compte = current($this->em->getRepository(Parametre::class)->findAll())->getCompteHebergement();
            if(!is_null($compte)){
                try {

                    $oldornew =  $request->get('noveauclientckeck');

                    if($oldornew=="on"){ 
                        
                            $nom =  $request->get('nom');
                            $prenom =  $request->get('prenom');
                            $datenais=  $request->get('datenaisance');
                            $datenaisance = new \DateTime($datenais);
                            $sex =  $request->get('sex');
                            $cni =  $request->get('cni');
                            $lieunaisance =  $request->get('lieunaisance');
                            $etatcivil =  $request->get('etatcivil');
                            $profession =  $request->get('profession');
                            $nationalite =  $request->get('nationalite');
                            $phone =  $request->get('phone');
                            $adresse =  $request->get('adresse');
                            $photo = $request->files->get("photo");
                            $user = new User();
                            $user->setNom($nom);
                            $user->setPrenom($prenom);
                            $user->setDatenaisance($datenaisance);
                            $user->setSexe($sex);
                            
                            if(empty($request->get("email"))){
                                $user->setEmail($nom."".$prenom."@hotelapp.com");
                            }else{
                                $user->setEmail($request->get("email"));
                            }
                            
                            $user->setCni($cni);
                            $user->setUsername(trim($nom."".$prenom));
                            $user->setPassword($this->passwordEncoder->encodePassword($user,"12345@abc"));
                            $user->setLieunaisance($lieunaisance);
                            $user->setEtatcivil($etatcivil);
                            
                            $user->setProfession($profession);
                            $user->setNationalite($nationalite);
                            $user->setPhone($phone);

                            $user->setAdresse($adresse);
                            $user->setType("CLIENT");
                            $this->em->persist($user);
                            $this->em->flush();
                            $client=$user;
                            

                    }else{
                        $clientid =  $request->get('client');
                        $client = $this->em->getRepository(User::class)->find($clientid);
                    }
                    
                    $dateat = $request->get("arriver");
                    $datedeb = new \DateTime($dateat);
                    
                    ///calcul duree du sejour
                    $quantite=$request->get("qte");
                    $packsejour=$request->get("sejourclient");
                    $tarif = $this->em->getRepository(Tarif::class)->find($packsejour);
                    $nombreheure=$tarif->getDuree()*intval($quantite);
                    $montant=$tarif->getPrix()*intval($quantite);
                    ///fin calcul durrer  du sejour 
        
                    $timeat = $request->get("arriver");
                    $timefin = new \DateTime($timeat);
                    
                    ////cacul du temp de fin 
                    $timefin->add(new DateInterval('PT'.$nombreheure.'H'));
                    /////fin calcule temps de fin 
        
                    $cham = $request->get("chambre");
                    $chambre = $this->em->getRepository(Chambre::class)->find($cham);
                    
                    //Create user if not exit
                    $user = new User();
                    if(is_null($client)){
                        $nom =  $request->get('nom');
                        $prenom =  $request->get('prenom');
                        $sex =  $request->get('sex');
                        $cni =  $request->get('cni');
                        
                        //$phone =  $request->get('phone');

                        $user->setNom($nom);
                        $user->setPrenom($prenom);
                        $user->setSexe($sex);
                        $user->setEmail($nom."".$prenom."@hotelapp.com");
                        
                        $user->setCni($cni);
                        $user->setUsername(trim($nom."".$prenom));
                        $user->setPassword($this->passwordEncoder->encodePassword($user,"12345@abc"));
                        //$user->setPhone($phone);
                        $user->setType("CLIENT");
                        

                        $this->em->persist($user);
                        $this->em->flush();
                        $client = $user;
                    }
                    $allocation = new Allocation();
        
                    $allocation->setDatedebut($datedeb);
                    $allocation->setDatefin($timefin);
        
                    //dd($chambre[0]->getType()->getTarifs());
        
                    $allocation->setMontant($montant);
                    $allocation->setOperateur($this->getUser());
                    $allocation->setAntene($this->getUser()->getAntene());
                    $allocation->setReduction(0);
                    $allocation->setOccupant($client);
                    $allocation->setChambre($chambre);
                    $allocation->setCompte($compte);
                    $allocation->setType($tarif->getType());
                    $allocation->setCreateat(new \DateTime('now'));
                    $this->em->persist($allocation);
                    $this->em->flush();
                    $this->setlog("AJOUTER","l'utilisateur ".$this->getUser()->getUsername().
                        " a ajouter une allocation ".$chambre->getNumero(),"ALLOCATION",$chambre->getId());
        
                    $result = array("success"=>true,"id"=>$allocation->getId());
                    return new JsonResponse($result);
                    
                }catch (\Exception $ex) {
                    $result = array("success"=>false,"id"=>-1);
                    return new JsonResponse($result);
                } 
            }else{
                $this->log("Aucun compte d'op??ration configur?? dans les param??tres.", $link);
            }
        }else{
            return $this->redirect('/login');
        }      
        
    } 


    /**
     * @Route("/filterallocation", name="filter-allocation", methods={"GET"})
    */
    public function filterallocation(Request $request){
      
        $link="filterallocation";
        $userg = $this->getUser();
        if($userg){
            try {
                    $typeID = $request->get("type") ? intval($request->get("type")) : null;
                    $type = null;
                
                    $jour = $request->get('jour'); 
                    
                    if($jour){
                    $jour = new DateTime($jour);
                    }else{
                    $jour = new Datetime();
                    }              
                    if($typeID){
                        $entrees = $this->em->getRepository(Allocation::class)->findBy(['createat'=>$jour, "chambre"=>$type]);
                        //dd($entrees);
                        // $allocations = $this->em->getRepository(Allocation::class)->find($typeID);
                        // dd($allocations);
                    }else{
                        //dd("nothing for the moment");
                    }
                
                    $entrees = $this->em->getRepository(Allocation::class)->findBy(['createat'=>$jour, "chambre"=>$type]);
                    $antennes = $this->em->getRepository(Entene::class)->findAll(); 
                    $typchambres = $this->em->getRepository(Typechambre::class)->findAll();
                    $allocations = $this->em->getRepository(Allocation::class)->findAll();
                    $chambres = $this->em->getRepository(Chambre::class)->findAll();
                    $tarif = $this->em->getRepository(Tarif::class)->findAll();
                    
                    $clients = $this->em->getRepository(User::class)->findBy(
                        ['type' => "CLIENT"]);

                
                    $data = $this->renderView('admin/allocation/filtre.html.twig', 
                    [
                        "antennes"=>$antennes,
                        "jour"=>$jour,
                    "typchambres" => $typchambres,
                        "chambres" => $chambres,
                        "tarifs" => $tarif,
                        "allocations" => $allocations,
                        "clients" => $clients,
                        "entrees" => $entrees
                    ]);
                    $this->successResponse("Journal comptable affich??", $link, $data);
            }catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirect('/login');
        }
    }


    
    /**
     * @Route("/dashboardallocation", name="dashboard-allocation", methods={"GET"})
    */
    public function dashboardallocation(Request $request){
      
        $link="dashboardallocation";
        $user = $this->getUser();
        if($user){
            try {
                if($user->getIsadmin()){
            
                    $allocations = $this->em->getRepository(Allocation::class)->findAll();
                    foreach ($allocations as $allocation) {

                        $heureactuel= new \DateTime();
                        $datefin = $allocation->getDatefin("datefin");
                    
                    }
            
                }else {
                    $allocations = $this->em->getRepository(Allocation::class)->findBy([
                        "antene"=>$user->getAntene()
                    ]);
                    foreach ($allocations as $allocation) {

                        $heureactuel= new \DateTime();
                        $datefin = $allocation->getDatefin("datefin");
                    
                    }
                }  
                $data = $this->renderView('admin/allocation/dashboardallocation.html.twig', 
                [
                    "allocations" => $allocations,
                ]);
                $this->successResponse("dashboardallocation affich??", $link, $data);
            }catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirect('/login');
        }
    }


    /**
     * @Route("/filtreallocation", name="filtre-allocation", methods={"GET"})
    */
    public function journal(Request $request){
        $link="filtreallocation";
        $userg = $this->getUser();
        if($userg){
            try {
        
                $chambreID = $request->get("chambre") ? intval($request->get("chambre")) : null;
                $chambre = null;
            

                $jour = $request->get('jour'); 
            
                if($jour){
                    $jour = new DateTime($jour);
                
                }else{
                    $jour = new Datetime();
                    
                }   
                if($chambreID){
                
                    $chambre = $this->em->getRepository(Chambre::class)->find($chambreID);
                    if($chambre){
                        $allocations = $this->em->getRepository(Allocation::class)->findBy(['chambre'=>$chambre,'datedebut'=>$jour]);
                        //dd($allocations);
                    }
                
                    
                }
                $allocations = $this->em->getRepository(Allocation::class)->findAll();
                $data = $this->renderView('admin/allocation/liste.html.twig', 
                [
                    "chambre"=>$chambre,
                    "jour"=>$jour,
                    
                    "allocations"=>$allocations
                
                ]);
                $this->successResponse("Journal comptable affich??", $link, $data);
            }catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirect('/login');
        }
    }
    


     /**
     * @Route("/getallchambrebytype ", name="getall-chambrebytype", methods={"GET"})
    */
    public function getallchambrebytype(Request $request){
        $userg = $this->getUser();
        if($userg){
            $typechambreID = $request->get("typechambre") ? intval($request->get("typechambre")) : null;
            $typechambre = null;
            if($typechambreID){
                $typechambre = $this->em->getRepository(Typechambre::class)->find($typechambreID);
                if ($typechambre) 
                {
                    $chambres = $this->em->getRepository(Chambre::class)->findBy(['type'=>$typechambreID]) ;
                    $list=array();
                    foreach ($chambres as $chambre) {
                    # code...
                    $list = array("id"=>$chambre->getId(), "numero"=>$chambre->getNumero());
                    

                }
                
                
                    return $this->json(['chambres'=>$list,'success'=> true] ); 
                    //return [0];
                /*  return $this->json($list);  */
                }else
                {
                    return $this->json(['success'=> false]); 
                }
                
                
            }else{
                return $this->json(['success'=> false]);
            }
        }else{
            return $this->redirect('/login');
        }
    }

    /**
     * @Route("/mesallocation" ,name="mes-allocation" ,methods={"GET"})
     */
    public function mesallocation(){
        //dd("received");
        $link="mesallocation";
        $user = $this->getUser();
        if($user){
            try {
                if($user->getIsadmin()){
                    $allocations = $this->em->getRepository(Allocation::class)->findAll();
                    $typchambres = $this->em->getRepository(Typechambre::class)->findAll();
                    $allocations = $this->em->getRepository(Allocation::class)->findAll();
                    //dd($allocations);
                    $chambres = $this->em->getRepository(Chambre::class)->findAll();
                    $tarif = $this->em->getRepository(Tarif::class)->findAll();
                    
                    $clients = $this->em->getRepository(User::class)->findBy(
                        ['type' => "CLIENT"]
                    );
                    
            
                }else{
                    $allocations = $this->em->getRepository(Allocation::class)->findAll();
                    $typchambres = $this->em->getRepository(Typechambre::class)->findAll();
                    $allocations = $this->em->getRepository(Allocation::class)->findBy([
                        "antene"=>$user->getAntene()
                    ]);
                    $chambres = $this->em->getRepository(Chambre::class)->findAll();
                    $tarif = $this->em->getRepository(Tarif::class)->findAll();
                    
                    $clients = $this->em->getRepository(User::class)->findBy(
                        ['type' => "CLIENT"]
                    );
                    
                
                }
                $data = $this->renderView('admin/allocation/mesallocation.html.twig', [
                    "typchambres" => $typchambres,
                    "chambres" => $chambres,
                    "tarifs" => $tarif,
                    "allocations" => $allocations,
                    "clients" => $clients
                
                ]);

                $this->successResponse("Liste de mesallocation", $link, $data);

            }catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            // dd($this->result);
            return $this->json($this->result);
        }else{
            return $this->redirect('/login');
        }
    }

    /**
      * @Route("/showallocation/{id}", name="show-allocation", methods={"GET"})
      *
      */
    public function viewAllocation($id) {
        $link="vueallocation";
        $userg = $this->getUser();
        if($userg){
            //dd($id);
            try {
                
                $allocation = $this->em->getRepository(Allocation::class)->find($id);
                //dd($allocation);
                $data = $this->renderView('admin/allocation/detail.html.twig', [ 
                    'allocation' => $allocation,
                ]);
                
                $this->successResponse("vueclient ", $link, $data);

            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            } 
            return $this->json($this->result);

        }else{
            return $this->redirect('/login');
        }
    }
	
	/**
     * @Route("/leaveallocation", name="leave-allocation", methods={"GET"})
     */
    public function leaveallocation()
    {
        $link="leaveallocation";
        $userg = $this->getUser();
        if($userg){
            try {
                //dd('ppp'); 
                $qb = $this->em->createQueryBuilder();
                $allocations = $qb->select('a')
                        ->from(Allocation::class, 'a')
                        /* ->where('a.datefin >= CURRENT_DATE() AND a.departavant= false') */
                        ->where('a.datefin >= CURRENT_DATE() AND a.departreel IS NULL')
                        ->orderBy('a.datefin', 'ASC');
                        $query = $qb->getQuery();
                $allocations = $query->execute();
                // dd($alocations);
                
                
                $data = $this->renderView('admin/allocation/journaliere.html.twig', [
                    "allocations" => $allocations
                ]);
                $this->successResponse("Liste des allocations ", $link, $data);
                
            }catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            // dd($this->result);
            return $this->json($this->result);
        }else{
            return $this->redirect('/login');
        }
    }


   
    /**
     * @Route("/leaveroom", name="leave-room", methods={"DELETE"})
     */
    public function leaveroom(Request $request): Response
    {
        $link="indexallocation";
        $userg = $this->getUser();
        $id = intval($request->get('id'));
        if($userg){
            try {
                $allocation = $this->em->getRepository(Allocation::class)->find($id);
                if($allocation->getDatefin() < (new Datetime('now'))){
                    $allocation->setDepartavant(true);
                }
                $allocation->setDepartreel(new Datetime('now'));
                $this->em->persist($allocation);
                $this->em->flush($allocation);
                $this->successResponse("chambre lib??r??e !",$link);  
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirect('/login');
        }
    }
}
