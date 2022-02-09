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
    public function indexallocation(Request $request)
    {
        $link="allocation";

        try {
               
            $type = $request->get("type");
            //dd($type);
            
            $chambres = $this->em->getRepository(Chambre::class)->findAll();
            if($type == "NUITEE"){
                if($this->getUser()->getIsadmin()){
                    $tarif = $this->em->getRepository(Tarif::class)->findBy([
                        'type' =>'NUITEE'
                    ]);
                }else{
                    $tarif = $this->em->getRepository(Tarif::class)->findBy([
                        'type' =>'NUITEE',
                        'antenne' => $this->getUser()->getAntene()
                    ]);
                } 
                
                $clients = $this->em->getRepository(User::class)->findBy(
                    ['type' => "CLIENT"]);
                //dd($clients);
                $data = $this->renderView('admin/allocation/index.html.twig', [
                    "chambres" => $chambres,
                    "tarifs" => $tarif,
                    "clients" => $clients
                ]);

            }else{
                if($this->getUser()->getIsadmin()){
                    $tarif = $this->em->getRepository(Tarif::class)->findBy([
                        'type' =>'SIESTE'
                    ]);
                }else{
                    $tarif = $this->em->getRepository(Tarif::class)->findBy([
                        'type' =>'SIESTE',
                        'antenne' => $this->getUser()->getAntene()
                    ]);
                } 
                
                $clients = $this->em->getRepository(User::class)->findBy(
                    ['username' => "divers"]);
                //dd($clients);
                $data = $this->renderView('admin/allocation/indexsieste.html.twig', [
                    "chambres" => $chambres,
                    "tarifs" => $tarif,
                    "clients" => $clients
                ]);

            }
            
            $this->successResponse("Liste des allocations ", $link, $data);
            
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }

    /**
     * @Route("/allouer/{id}", name="allouer", methods={"GET"})
     */
    public function allouer($id)
    {
        $link="allocation";

        try {
               
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
            $this->log("Aucun compte d'opération configuré dans les paramètres.", $link);
        }       
        
    }


    /**
     * @Route("/allocationadd", name="allocation-add", methods={"POST"})
     */
    public function allocationadd(Request $request)
{       $id = $request->get("id");
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
                   

                }/* else if($radio=="cash")
                {
                    $solde = $client->getSolde("solde");
                    $montant=$tarif->getPrix()*intval($quantite);
                    

                    $montantcash=$request->get("montantcash");
                    $clientid =  $request->get('client');
                    $client = $this->em->getRepository(User::class)->find($clientid);
                    
                    if($montantcash==$montant){
                    
                        $transact = new Transaction();
                    
                      
                        $transact->setMontant($montantcash);
                        $transact->setClient($client);
                        
                        $transact->setType("CASH");
                        $transact->setCreatedat(new \DateTime('now'));
                        $transact->setCreatedby($this->getUser());
                      
                        $this->em->persist($transact);
                        $this->em->flush();
                    }else {
                       
                        $this ->addFlash( 'danger','vous devez payer la totalité en espèce');
                        return $this->redirectToRoute('index-allocation',[]);

                    }
                   
                } */
                
               

                ///fin calcul durrer  du sejour 
    
                $timeat = $request->get("arriver");
                $timefin = new \DateTime($timeat);
                
                ////cacul du temp de fin 
                $timefin->add(new DateInterval('PT'.$nombreheure.'H'));
                /////fin calcule temps de fin 
    
                $cham = $request->get("chambre");
                $chambre = $this->em->getRepository(Chambre::class)->find($cham);
                //dd($chambre);


                ///etat  
                 ///$id = $request->get("id");  
                
                /// $reservations = $this->em->getRepository(Reservation::class)->find($id);
               /// $etat = $reservations->getEtat("etat");
                
                 
               
               /// if($etat=="Non Traiter"){ 
                ///    $reservations->setEtat("Traiter");
                   
                ///}else{
                ///    $reservations->setEtat("Non Traiter");
                  
                ///}
               /// $this->em->persist($reservations);
               
            ///etat stop
          /*   $id = $request->get("id");
            $alluser = $this->em->getRepository(User::class)->find($id);
            dd($alluser);
            $etat = $alluser->getSolde("solde"); */
               

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
                return new JsonResponse($result);
                
            }catch (\Exception $ex) {
                $result = array("success"=>false,"id"=>$ex->getMessage());
                return new JsonResponse($result);
            } 
        }else{
            $this->log("Aucun compte d'opération configuré dans les paramètres.", $link);
        }       
        
    }

    /**
     * @Route("/validereservation", name="valide-reservation", methods={"POST"})
     */
    /* public function validereservation(Request $request)
    {
        $id = $request->get("id");
        $link="validereservation";
        $compte = current($this->em->getRepository(Parametre::class)->findAll())->getCompteHebergement();
        if(!is_null($compte)){
            try {

                $clientid =  $request->get('client');
                $client = $this->em->getRepository(User::class)->find($clientid);
                
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
                //dd($chambre);
                
                $radio=$request->get("radio");
                dd($radio);

                ///etat  
                 $id = $request->get("id");  
                
                 $reservations = $this->em->getRepository(Reservation::class)->find($id);
                 $etat = $reservations->getEtat("etat");
                
                 
               
                if($etat=="Non-traiter"){ 
                    $reservations->setEtat("Traiter");
                   
                }else{
                    $reservations->setEtat("Non-traiter");
                  
                }
                $this->em->persist($reservations);
               
            ///etat stop
               

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
                    "a ajouter une allocation ".$chambre->getNumero(),"ALLOCATION",$chambre->getId());
    
                $result = array("success"=>true,"id"=>$allocation->getId());
                return new JsonResponse($result);
                
            }catch (\Exception $ex) {
                $result = array("success"=>false,"id"=>-1);
                return new JsonResponse($result);
            } 
        }else{
            $this->log("Aucun compte d'opération configuré dans les paramètres.", $link);
        }       
        
    } */

    /**
     * @Route("/validereservation", name="valide-reservation", methods={"POST"})
     */
    public function validereservation(Request $request)
{       $id = $request->get("id");
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
                if($etat=="Non-traité"){ 
                    $reservations->setEtat("Traité");
                   
                    
                }else{
                    $reservations->setEtat("Traité");
                
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
            $this->log("Aucun compte d'opération configuré dans les paramètres.", $link);
        }       
        
    }





    
    public function checkdisponibility($heurearriverclient,$heurefinclient,$heuredebut,$heurefin)
    {
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
    }

    /**
     * @Route("/getalltarif", name="getalltarif", methods={"GET"})
     */
    public function getalltarif(Request $request){
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
    }

    /**
     * @Route("/getsiestetarif", name="getsiestetarif", methods={"GET"})
     */
    public function getsiestetarif(Request $request){
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
    }

	/**
     * @Route("/getcanpay", name="getcanpay", methods={"GET"})
     */
    public function getcanpay(Request $request){
        $id = $request->get("id");
        $client = $this->em->getRepository(User::class)->find($id);
        $solde = $client->getSolde(); 
        
        return new JsonResponse($solde);
    }
	
    /**
     * @Route("/getallchambrelibre", name="getallchambrelibre", methods={"POST"})
     */
    public function getallchambrelibre(Request $request){
       
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
        
        
    }

    public function checkifroomfree($chambreid,$heuredebut,$heurefin){
        
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
            
    }

    /**
     * @Route("/printrecuallocation", name="print-recu", methods={"GET"})
     */
    public function printrecus(Request $request)
    {
        $idallocation=$request->get("id");
        $allocation = $this->em->getRepository(Allocation::class)->find($idallocation);
        
        return $this->printRecu($allocation);
    }
	
	/**
     * @Route("/listeallocation", name="liste-allocation", methods={"GET"})
     */
    public function listeallocation()
    {
        $link="listallocation";
        $user = $this->getUser();
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

    }

    /**
     * @Route("/printallocation", name="print-allocation")
     */
    public function printallocation(){
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
    }
	
	
	 /**
     * @Route("/allocationreser", name="allocation-reser", methods={"POST"})
     */
    public function allocationreser(Request $request)
    {
        $link="allocation";
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
            $this->log("Aucun compte d'opération configuré dans les paramètres.", $link);
        }       
        
    } 


    /**
     * @Route("/filterallocation", name="filter-allocation", methods={"GET"})
    */
    public function filterallocation(Request $request){
      
        $link="filterallocation";
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
                $this->successResponse("Journal comptable affiché", $link, $data);
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }


    
    /**
     * @Route("/dashboardallocation", name="dashboard-allocation", methods={"GET"})
    */
    public function dashboardallocation(Request $request){
      
        $link="dashboardallocation";
        $user = $this->getUser();
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
                $this->successResponse("dashboardallocation affiché", $link, $data);
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }


    /**
     * @Route("/filtreallocation", name="filtre-allocation", methods={"GET"})
    */
    public function journal(Request $request){
    $link="filtreallocation";
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
     
         
    }else{
        /* $chambre = $this->em->getRepository(Chambre::class)->findBy(['type'=>$typechambreID]);
        $allocations = $this->em->getRepository(Allocation::class)->findBy(['createat'=>$jour]); */
    } 
    $allocations = $this->em->getRepository(Allocation::class)->findAll();
    $data = $this->renderView('admin/allocation/liste.html.twig', 
    [
        "chambre"=>$chambre,
        "jour"=>$jour,
        
        "allocations"=>$allocations
       
    ]);
    $this->successResponse("Journal comptable affiché", $link, $data);
}catch (\Exception $ex) {
    $this->log($ex->getMessage(), $link);
}
return $this->json($this->result);
    }
    


     /**
     * @Route("/getallchambrebytype ", name="getall-chambrebytype", methods={"GET"})
    */
    public function getallchambrebytype(Request $request){
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
    }

    /**
     * @Route("/mesallocation" ,name="mes-allocation" ,methods={"GET"})
     */
    public function mesallocation(){
        //dd("received");
        $link="mesallocation";
        $user = $this->getUser();

        try {
            if($user->getIsadmin()){
            $allocations = $this->em->getRepository(Allocation::class)->findAll();
            $typchambres = $this->em->getRepository(Typechambre::class)->findAll();
            $allocations = $this->em->getRepository(Allocation::class)->findAll();
            //dd($allocations);
            $chambres = $this->em->getRepository(Chambre::class)->findAll();
            $tarif = $this->em->getRepository(Tarif::class)->findAll();
            
            $clients = $this->em->getRepository(User::class)->findBy(
                ['type' => "CLIENT"]);
            
          
        }else{
            $allocations = $this->em->getRepository(Allocation::class)->findAll();
            $typchambres = $this->em->getRepository(Typechambre::class)->findAll();
            $allocations = $this->em->getRepository(Allocation::class)->findBy([
                "antene"=>$user->getAntene()
            ]);
            $chambres = $this->em->getRepository(Chambre::class)->findAll();
            $tarif = $this->em->getRepository(Tarif::class)->findAll();
            
            $clients = $this->em->getRepository(User::class)->findBy(
                ['type' => "CLIENT"]);
            
           
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
    }

    /**
      * @Route("/showallocation/{id}", name="show-allocation", methods={"GET"})
      *
      */
    public function viewAllocation($id) {
        $link="vueallocation";
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
    }
	
	/**
     * @Route("/leaveallocation", name="leave-allocation", methods={"GET"})
     */
    public function leaveallocation()
    {
        $link="leaveallocation";

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
    }


   
    /**
     * @Route("/leaveroom", name="leave-room")
     */
    public function leaveroom(Request $request): Response
    {
        $link="leaveroom";

        try {
            
            $id = $request->get("id");
            $alocation = $this->em->getRepository(Allocation::class)->find($id);
            if($alocation->getDatefin() < (new Datetime('now'))){
                $alocation->setDepartavant(true);
            }
            //dd($alocation);
            $alocation->setDepartreel(new Datetime('now'));
           // $alocation->setDepartreel(new Datetime('now'));
            $this->em->persist($alocation);
            $this->em->flush($alocation);
            
            
            $this->successResponse("chambre liberer !",$link);  
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), "leave-allocation");
        }
       // dd($this->result);
        return $this->json($this->result);
    }
}
