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
use App\Entity\Chambre;
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
            $user = $this->getUser();
            $totalchambre = 0;
            $totalallocation = 0;
            $staatistiquejour = 0;
            $satistiquglobaleenteneparmois = 0;
            $staatistiquemois = 0;
            $totalemployes = 0;
            $totalclients = 0;
            $statentne = array(); 

            if($user){
                if($user->getIsAdmin()){
                    $antennes = $this->getAllAntennes();

                    foreach ($antennes as $antenne) {
                        $montant = $this->em->getRepository(Allocation::class)->countaportAllocation($antenne->getId());
                        array_push($statentne, ["entene"=>$antenne->getNom(),"total"=>$montant]);                        
                    }

                    $totalchambre = count($this->em->getRepository(Chambre::class)->findAll());
                    $totalallocation = count($this->em->getRepository(Allocation::class)->findAll());
                    $totalemployes = count($this->em->getRepository(User::class)->findBy(['type' => "EMPLOYE"]));
                    $totalclients = count($this->em->getRepository(User::class)->findBy(['type' => "CLIENT"]));
				    $totalreservations = count($this->em->getRepository(Reservation::class)->findAll());
                    $staatistiquejour = $this->em->getRepository(Allocation::class)->countaportAllocationparjour();
                    $staatistiquemois = $this->em->getRepository(Allocation::class)->countaportAllocationparmois();
                    $satistiquglobaleenteneparmois = $this->em->getRepository(Allocation::class)->countaportAllocationbymoisantene();

                }else{
                    $totalchambre=count($this->getUser()->getAntene()->getChambres());
                    $totalallocation=count($this->getUser()->getAntene()->getAllocations());
                    $totalemployes = count($this->em->getRepository(User::class)->findBy(['type' => "EMPLOYE",'antene' => $user->getAntene()]));
                    $totalclients = count($this->em->getRepository(User::class)->findBy(['type' => "CLIENT",'antene' => $user->getAntene()]));
                    $staatistiquejour=$this->em->getRepository(Allocation::class)->countaportAllocationparjour($user->getAntene()->getId());
                    $satistiquglobaleenteneparmois = $this->em->getRepository(Allocation::class)->countaportAllocationbymoisantene($user->getAntene()->getId());
                    $staatistiquemois=$this->em->getRepository(Allocation::class)->countaportAllocationparmois($user->getAntene()->getId());
				    $totalreservations = count($this->em->getRepository(Reservation::class)->findBy(['antene' => $user->getAntene()]));
                    $montant = $this->em->getRepository(Allocation::class)->countaportAllocation($user->getAntene()->getId());
                        array_push($statentne, ["entene"=>$user->getAntene()->getNom(),"total"=>$montant]);
                }
            }else{
                return $this->redirect("/login");
            }

            
            // if(empty($staatistiquejour[1])){
            //     $staatistiquejour[1]=0;
            // }
            // $bilangneraleantene = array(); 
            /////calcul bilan par mois de lantene de lutilisateur  
            
            //array_push($bilangneraleantene,array("mois"=>"JANV","total"=>1000));
            /////fin calcule bilan par moi 
            //dd($staatistiquemois[1]);
            
            $session->set('userdroit', $droituser);
            
            //dd($statentne);
            return $this->render('admin/dashboard/index.html.twig', [
                "totalchambre" => $totalchambre,
                "totalallocation" => $totalallocation,
                "totalemploye" => $totalemployes,
                "totalclient" => $totalclients,
				"totalreservation" => $totalreservations,
                "statistiqueentene"=>$statentne,
                "bilanjournalier"=>$staatistiquejour,
                "staatistiquemois"=>$staatistiquemois,
                "satistiquglobaleenteneparmois"=>$satistiquglobaleenteneparmois,
                "parametre" => $this->parametre
            ]); 
        }

        // public function getresutstatmois(){

        // }

        
    }

    
     
}
