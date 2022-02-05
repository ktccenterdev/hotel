<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Tarif;
use App\Entity\Entene;
use App\Entity\Chambre;
use App\Entity\Reservation;
use App\Entity\Typechambre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ReservationController extends DefaultController
{
   

    //reservation at frontvue
    /**
     * @Route("/reservation", name="extern-reservation",methods={"GET"})
     */
    public function reservation()
    {
       
        $types = $this->em->getRepository(Typechambre::class)->findAll(); 
        $chambres = $this->em->getRepository(Chambre::class)->findAll(); 
        $typchambres = $this->em->getRepository(Typechambre::class)->findAll();
        $tarif = $this->em->getRepository(Tarif::class)->findAll();
            
        

        return $this->render('reservation.html.twig', [
            "types" => $types,
            "chambres" => $chambres,
            "typchambres" => $typchambres,
            "tarifs" => $tarif,
                
        ]); 
    }
	
	/**
     * @Route("/entenereser/{id}", name="entenereser",methods={"GET"})
     */
    public function entenereser(Request $request,$id)
    {
       //dd("dsdvd");
       $id1 = $request->get("id1");
       $id2 = $request->get("id");
       //dd($idd);
      
      

       $chambreses = $this->em->getRepository(Chambre::class)->find($id2);
     //dd($chambreses);
      $entenes=$this->em->getRepository(Entene::class)->find($id1);
      //dd($entenes);
       
        $types = $this->em->getRepository(Typechambre::class)->findAll(); 
        $chambres = $this->em->getRepository(Chambre::class)->findAll(); 
        $typchambres = $this->em->getRepository(Typechambre::class)->findAll();
        $tarif = $this->em->getRepository(Tarif::class)->findAll();
            
        

        return $this->render('tryreservation.html.twig', [
            "types" => $types,
            "chambres" => $chambres,
            "chambreses" => $chambreses,
            "typchambres" => $typchambres,
            "tarifs" => $tarif,
           "entenes" => $entenes,

                
        ]); 
    }

    /**
     * @Route("/indexreservation", name="index-reservation", methods={"GET"})
     */
    public function indexreservation(Request $request)
    {
        $link="reservation";
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $listReservations = array();
        try {            
            $user = $this->getUser(); 
            $types = $this->em->getRepository(Typechambre::class)->findAll();
            
            if($user && $user->getIsadmin()){
                $antennes = $this->em->getRepository(Entene::class)->findAll();
                foreach($antennes as $antenne){
                    $reservationsTraitees = $this->em->getRepository(Reservation::class)->filterByDate($debut, $fin, $antenne->getId(), 'Traité');
                    $reservationsNonTraitees = $this->em->getRepository(Reservation::class)->filterByDate($debut, $fin, $antenne->getId(), 'Non-traité');
                    $listReservations[] = [
                        'antenne'=>$antenne, 
                        'reservationsTraitees' => $reservationsTraitees,
                        'reservationsNonTraitees' => $reservationsNonTraitees
                    ];
                }                   
                $data = $this->renderView('admin/reservation/adminindex.html.twig', [
                    "antennes" => $antennes,
                    "listReservations" => $listReservations,
                    "types" => $types,
                    "debut" =>$debut,
                    "fin"=>$fin
                ]);
                $this->successResponse("Liste des reservations ", $link, $data);
            }else{
                $antenne = $user->getAntene();                
                $reservationsNonTraitees = $this->em->getRepository(Reservation::class)->filterByDate($debut, $fin, $antenne->getId(), 'Non-traité');
                $reservationsTraitees = $this->em->getRepository(Reservation::class)->filterByDate($debut, $fin, $antenne->getId(), 'Traité');
              //  dd($reservations);
                $data = $this->renderView('admin/reservation/index.html.twig', [
                    "reservationsNonTraitees" => $reservationsNonTraitees,
                    "reservationsTraitees" => $reservationsTraitees,
                    "types" => $types,
                    "debut" =>$debut,
                    "fin"=>$fin,
                    "antenne"=>$antenne
                ]);
                $this->successResponse("Liste des reservations ", $link, $data);
            }
                

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }


    /**
     * @Route("/extreservation", name="list-reservation", methods={"GET"})
     */
    public function listreservation()
    {
        $link="listreservation";

        try {
            
            $user = $this->getUser(); 
            if($user->getIsadmin()){
                $entene = $this->em->getRepository(Entene::class)->findAll();
                $reservations = $this->em->getRepository(Reservation::class)->findBy(['createby' => null]);
                //dd($reservations);
               
               /*  $data = $this->renderView('admin/reservation/adminexterne.html.twig', [ */
               $data = $this->renderView('admin/reservation/externe.html.twig', [
                    "reservations" => $reservations,
                    "entenes" => $entene,
                    
                ]);
                $this->successResponse("Liste des reservations externe ", $link, $data);
            }else{
                $entene = $this->em->getRepository(Entene::class)->findAll();
                $reservations = $this->em->getRepository(Reservation::class)->findBy(['antene' => $user->getAntene(),'createby' => null ]);
                
                $data = $this->renderView('admin/reservation/externe.html.twig', [
                    "reservations" => $reservations,
                    "entenes" => $entene,
                    
                ]);
                $this->successResponse("Liste des reservations externe ", $link, $data); 
            }
                

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }


    /**
     * @Route("/addreservation", name="add-reservation", methods={"POST"})
     */
    public function addreservation(Request $request)
    {
        $link="indexreservation";

        try {
            	
            $user = $this->getUser(); 
            if($user){
                $client_id = $request->get('client');

                $client = $this->em->getRepository(User::class)->find($client_id);
                $type= $request->get('typech');
                $anten= $request->get('antene');
                if($anten){
                    $antene = $this->em->getRepository(Entene::class)->find($anten);
                }else{
                    $antene = $this->getUser()->getAntene();
                }
                
                $typech = $this->em->getRepository(Typechambre::class)->find($type);
               /*  $type = $typech->getType(); */

               /*  $tarif = $this->em->getRepository(Tarif::class)->find($type); */

                $dateat = $request->get("dateat");
                $datedeb = new \DateTime($dateat);
                
                $dateto = $request->get("dateto");
                $datefin = new \DateTime($dateto);

                $heurea = $request->get("heurea");
                $timedebut = new \DateTime($heurea);
                //dd($timedebut);
                
                $heuref = $request->get("heuref");
                $timefin = new \DateTime($heuref);

                $reservation = new Reservation();

                $reservation->setDatedariver($datedeb);
                $reservation->setDatedepart($datefin);
                $reservation->setHeuredariver($timedebut);
                $reservation->setHeuredepart($timefin);
                $reservation->setMontan($request->get('client'));
                //$reservation->setMontan($tarif->getPrix());
                //$reservation->setReduction($request->get(''));
                $reservation->setTypechambre($typech);
                $reservation->setClient($client);
                $reservation->setCreateat(new \DateTime('now'));
                $reservation->setEtat("Non-traité");
                $reservation->setCreateby($user);
                $reservation->setAntene($antene);

                $this->em->persist($reservation);
                $this->em->flush();
                $this->setlog("AJOUTER","L'utilisateur ".$this->getUser()->getUsername().
                " a ajouter LA reservation  ".$reservation->getChambre(),"RESERVATION",$reservation->getId()); 
                $this->successResponse("Liste des reservations ", $link);

            }
                

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), "index-reservation");
        }
       // dd($this->result);
        return $this->json($this->result);
    }


     /**
     * @Route("/indexfront", name="index-front", methods={"GET"})
     */
    public function reservationint()
    {
        $link="reservation1";

        try {
            $user = $this->getUser(); 
            //dd($user);
            $chambres = $this->em->getRepository(Chambre::class)->findAll();
            $clients = $this->em->getRepository(User::class)->findBy(
                ['type' => "CLIENT"]);
            //dd($clients);
            $data = $this->renderView('admin/reservation/reservationfront.html.twig', [
                "chambres" => $chambres,
                "clients" => $clients
            ]);
            $this->successResponse("indexfront ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }



    /**
     * @Route("/admin/client/active", name="active-client")
     */
    public function externeclient(Request $request): Response
    {
        $link="extreservation";

        try {
          
                $id = $request->get("id");
                $reservations = $this->em->getRepository(Reservation::class)->find($id);
                $user = $this->getUser(); 

                $statut = $reservations->getCreateby("createby");
             

                if(!$statut){
                    $reservations->setCreateby($user);
                   // dd($reservations);
                    
                }else
                {
                    $reservations->setCreateby(NULL);
                }

                $this->em->persist($reservations);
                $this->em->flush($reservations);
               
               
                $this->successResponse("Reservation Activer !",$link);  
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), "list-reservation");
            }
           // dd($this->result);
            return $this->json($this->result);
        }





    /**
     * @Route("/addreservationfr", name="add-reservationfr", methods={"POST"})
     */
    public function indexadd(Request $request,TranslatorInterface $translator)
    {

        $id = $request->get("id");
		$entenes = $this->em->getRepository(Entene::class)->find($id);
        //dd($entenes);
		
        $nom = $request->get("nom");
		//$entenne = $request->get("entenne");
		
        $prenom = $request->get("prenom");
        $user = new User();
        $user->setNom($nom);
        $user->setPrenom($prenom);
        
        if(empty($request->get("email"))){
            $user->setEmail($nom."".$prenom."@hotelapp.com");
        }else{
            $user->setEmail($request->get("email"));
        }
        
        $user->setUsername(trim($nom."".$prenom));
        
        $user->setPhone($request->get("phone"));
        $user->setType("CLIENT");
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,'externe'
            )
        );
		$user->setIsadmin(false);
		$user->setSolde(0);
        $this->em->persist($user);
        // dd($reservation);
        $this->em->flush();
        

        $titre = $request->get("titre");
        $qte = $request->get("qte");
       // dd($qte);

        $sejourclient = $request->get("sejourclient");
        //dd($sejourclient);

        $typefr = $request->get("typebed");
        $type = $this->em->getRepository(Typechambre::class)->find($typefr);

        /* $chambre_id= $request->get('chambre_id');
        $chambre=$this->em->getRepository(Chambre::class)->find($chambre_id); */

        $datedariver = $request->get("datedariver");
        $dateda = new \DateTime($datedariver);
        $datedepart = $request->get("datedepart");
        $datede= new \DateTime( $datedepart);

            
        $heuredariver = $request->get("heuredariver");
        $heuredar = new \DateTime( $heuredariver);
        $heuredepart = $request->get("heuredepart");
        $heuredep = new \DateTime($heuredepart);

        
        $reservation = new Reservation();

        
        $reservation->setTitre($titre);
        /* $reservation->setChambre($chambre);
         */

        $reservation->setDatedariver($dateda);
        $reservation->setDatedepart($datede);

        $reservation->setHeuredariver($heuredar);
        $reservation->setHeuredepart($heuredep);
        $reservation->setTypechambre($type);
        $reservation->setMontan($sejourclient);
        $reservation->setQuantite($qte);
		$reservation->setAntene($entenes);

       
        $reservation->setClient($user);
        $reservation->setEtat("Non-traité");
        

       /*  $reservation->setMontan($request->get("montan"));
        $reservation->setReduction($request->get("reduction")); */
        
            
        $this->em->persist($reservation);
        // dd($reservation);
        $this->em->flush();

        //$message = $translator->trans('Reservation done succesfully');
        $this ->addFlash('success', 'Reservation effectuée avec succes' );

        return $this->redirectToRoute('myantenne',[ "id"=>$id,  /* "id1"=>$id,  "id1"=>$id */]);

    }



    
	 /**
      * @Route("/showreservation/{id}", name="show-reservation")
      *
      */
      public function showreservation($id) {
           
        $link="vuereservation";

        try {
           
          
           
            //$type = $this->em->getRepository(Typechambre::class)->find($id);
            $reservations = $this->em->getRepository(Reservation::class)->find($id);
            //$types =$this->em->getRepository(Typechambre::class)->findAll();
            
          
          
           
            $data = $this->renderView('admin/reservation/view.html.twig', [
                     'reservations' => $reservations,
                  
            ]);
            $this->successResponse("vuereservation ", $link, $data);


         } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return $this->json($this->result);
      

    }   




    
    /**
      * @Route("/deletereservation/{id}", name="delete-reservation")
      *
      */
      public function deletereservation($id) {
           
        $link="indexreservation";

        try {
            
            $em = $this->getDoctrine()->getManager();
            $reservations = $this->em->getRepository(Reservation::class)->find($id);
            
            if (!$reservations) {
                throw $this->createNotFoundException(
                    'There are no reservations with the following id: ' . $id
                );
            }
            $em->remove($reservations);
            $em->flush();
            $this->setlog("SUPPRIMER","Le Produit ".$this->getUser()->getUsername().
            " a supprimer la reservation ".$reservations->getNom(),"RESERVATION",$reservations->getId());
            $this->successResponse("reservations supprimé ", $link);


        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return $this->json($this->result);
      

    }   



    
    /**
      * @Route("/editreservation/{id}/edit", name="edit-reservation")
      *
      */
      public function editreservation($id) {
           
        $link="vuereservation";

        try {
         
          
           
            $reservations = $this->em->getRepository(Reservation::class)->find($id);
            $types = $this->em->getRepository(Typechambre::class)->findAll(); 
            $clients = $this->em->getRepository(User::class)->findBy(['type' => 'CLIENT']); 
           
            $data = $this->renderView('admin/reservation/edit.html.twig', [
                     'reservations' => $reservations,
                     "types" => $types,
                     'clients' => $clients,
                  
            ]);
            $this->successResponse("vuereservation ", $link, $data);


        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return $this->json($this->result);
      

    }   



    /**
      * @Route("/editRreservation/edit", name="edit-Rreservation")
      *
      */
      public function editRreservation(Request $request) : Response
      {
          $link="indexreservation";
          
        //dd("sdvsdv");
        
        $id = $request->get("id");
        try {
            $user = $this->getUser(); 

            $client_id = $request->get('client');
            $client = $this->em->getRepository(User::class)->find($client_id);

            $type= $request->get('typech');
            $typech = $this->em->getRepository(Typechambre::class)->find($type);
          
            $dateat = $request->get("dateat");
            $datedeb = new \DateTime($dateat);
            
            $dateto = $request->get("dateto");
            $datefin = new \DateTime($dateto);

            $heurea = $request->get("heurea");
            $timedebut = new \DateTime($heurea);
          
            
            $heuref = $request->get("heuref");
            $timefin = new \DateTime($heuref);
         
         
            $reservation= $this->em->getRepository(Reservation::class)->find($id);
           
            $reservation->setDatedariver($datedeb);
            $reservation->setDatedepart($datefin);
            $reservation->setHeuredariver($timedebut);
            $reservation->setHeuredepart($timefin);
            $reservation->setMontan($request->get('client'));
            $reservation->setTypechambre($typech);
            $reservation->setClient($client);
            $reservation->setCreateat(new \DateTime('now'));
            $reservation->setEtat("Non-traiter");
            $reservation->setCreateby($user);

            $this->em->persist($reservation);
            $this->em->flush();
           


        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       
        //return $this->json($this->result);
        $this ->addFlash( 'success', 'Reservation Modifier avec succes' );
        return $this->redirectToRoute('index-reservation',[]);
      

    } 



    /**
     * @Route("/printexterne", name="print-externe")
     */
    public function printexterne(){
        $link="client";
          $reservations = $this->em->getRepository(Reservation::class)->findBy(['createby' => null]);
           
            $antene = $this->getUser()->getAntene();
            $template = $this->renderView('admin/print/printexterne.pdf.twig', [
                "reservations" => $reservations,
                "antene" => $antene,
            ]);
        
       return $this->returnPDFResponseFromHTML($template, "Liste des externe", "L"); 
    }


    /**
     * @Route("/printinterne", name="print-interne")
     */
    public function printinterne(){
        $link="client";
         
           $qb = $this->em->createQueryBuilder();
           $qb->select('c')
           ->from(Reservation::class,'c')
           ->where($qb->expr()->isNotNull('c.createby'));
           $query = $qb->getQuery();
           $reservations = $query->execute();
           
        $antene = $this->getUser()->getAntene();
        $template = $this->renderView('admin/print/printinterne.pdf.twig', [
            "reservations" => $reservations,
            "antene" => $antene,
        ]);
        
       return $this->returnPDFResponseFromHTML($template, "Liste des interne", "L"); 
    }

 







}
