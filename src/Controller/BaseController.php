<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EnteneRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\User;
use App\Entity\Log;
use App\Entity\Allocation;
use App\Entity\Entene;
use App\Entity\Reservation;


class BaseController extends DefaultController
{
   

    /**
     * @Route("/logindex", name="log-index")
     */
    public function logindex()
    {
        $link="allocation";
        try {
            $logs = $this->em->getRepository(Log::class)->findBy(array(), array('createat'=>'desc'));
            $data = $this->renderView('admin/log/index.html.twig', [
                "logs"=>$logs
            ]);
            $this->successResponse("Liste des Logs ", $link, $data);
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return $this->json($this->result);
      
    }


    /**
     * @Route("/loginn", name="indexpage")
     */
    public function index()
    {
        
      /*   return $this->redirectToRoute('app_login'); */
      return $this->render('intro/index.html.twig', []);
      
    }
   
       /**
     * @Route("/", name="indexfront", methods={"GET"})
     */
    public function indexfront()
    {
        
        $entenes=$this->em->getRepository(Entene::class)->findAll();
        
        return $this->render('intro/index.html.twig', [
            "entenes" => $entenes
        ]); 
    }
   



    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboard(Request $request)
    {
        $routeName = $request->get('_route');

        if(!$this->checklogin()){
            return $this->redirect("/login");
        }else{
            $this->setlog("the user login in system","authantification",1);
            $droituser=array();   
            foreach ($this->getUser()->getRole()->getActionRoles() as $value) {
                if($value->getEtat()){
                    array_push($droituser,$value->getAction()->getCle());
                }
                
            }  
            $session = new Session();
            if(empty($session)){
                $session->start();
            }
            /////calule des statistique 
            
            $totalchambre=count($this->getUser()->getAntene()->getChambres());
            $totalallocation=count($this->getUser()->getAntene()->getAllocations());
            $employer = $this->em->getRepository(User::class)->findBy(
                ['type' => "EMPLOYE",'antene' => $this->getUser()->getAntene()->getId()]);
            $client = $this->em->getRepository(User::class)->findBy(
                    ['type' => "CLIENT",'antene' => $this->getUser()->getAntene()]);
            ///fin calcule des statistique 
            //$session->start();
            ////
            $idanteneok=$this->getUser()->getAntene()->getId();
            $staatistiquejour=$this->em->getRepository(Allocation::class)->countaportAllocationparjour($idanteneok);
            
            
            if(empty($staatistiquejour[1])){
                $staatistiquejour[1]=0;
            }
            $bilangneraleantene = array(); 
            /////calcul bilan par mois de lantene de lutilisateur  
            $satistiquglobaleenteneparmois=$this->em->getRepository(Allocation::class)->countaportAllocationbymoisantene($idanteneok);
            
            //array_push($bilangneraleantene,array("mois"=>"JANV","total"=>1000));
            /////fin calcule bilan par moi 
            $staatistiquemois=$this->em->getRepository(Allocation::class)->countaportAllocationparmois($idanteneok);
            //dd($staatistiquemois[1]);
            $entenes=[];
            if ($this->getUser()->getIsadmin()) {
                $entenes = $this->em->getRepository(Entene::class)->findAll();
				$reservations = $this->em->getRepository(Reservation::class)->findAll();
				$client = $this->em->getRepository(User::class)->findBy(
                    ['type' => "CLIENT"]);
            }else{
                array_push($entenes, $this->getUser()->getAntene());
				$reservations = $this->em->getRepository(Reservation::class)->findBy(['antene' => $this->getUser()->getAntene(),'createby'=> !null ]);
				$client = $this->em->getRepository(User::class)->findBy(
                    ['type' => "CLIENT",'antene' => $this->getUser()->getAntene()]);
				
            }
            $statentne = array(); 
            //$items[] = $curentitems;
            foreach ($entenes as $value) {
                $curent=$this->em->getRepository(Allocation::class)
                ->countaportAllocation($value->getId());
                if(!empty($curent[1])){
                    $items=array("entene"=>$value->getNom(),"total"=>$curent[1]);
                }else{
                    $items=array("entene"=>$value->getNom(),"total"=>0);  
                }
                
                $statentne[] = $items;
                //dd($curent[1]);
            }
			
            
            $session->set('userdroit', $droituser);
            
            return $this->render('admin/dashboard/index.html.twig', [
                "totalchambre" => $totalchambre,
                "totalallocation" => $totalallocation,
                "totalemploye" => count($employer),
                "totalclient" => count($client),
				"totalreservation" => count($reservations),
                "statistiqueentene"=>$statentne,
                "bilanjournalier"=>$staatistiquejour[1],
                "staatistiquemois"=>$staatistiquemois[1],
                "satistiquglobaleenteneparmois"=>$satistiquglobaleenteneparmois,
                "parametre" => $this->parametre
            ]); 
        }

        // public function getresutstatmois(){

        // }

        
    }

    
     
}
