<?php

namespace App\Controller;

use App\Entity\Entene;
use App\Entity\Chambre;
use App\Entity\Reservation;
use App\Entity\Allocation;
use App\Entity\Typechambre;
use App\Controller\DefaultController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChambreController extends DefaultController
{
    /**
     * @Route("/indexchambre", name="index-chambre", methods={"GET"})
     */
    public function indexchambre()
    {
        $link="indexchambre";

        try {
            $user = $this->getUser();
            if ($user) {
                # code...
                if($user->getIsadmin()){
                    $types =$this->em->getRepository(Typechambre::class)->findAll();
                    $entenes =$this->em->getRepository(Entene::class)->findAll();
    
                    $chambres=$this->em->getRepository(Chambre::class)->findAll();
                    $this->setlog("Liste des chambres","L'utilisateur ".$this->getUser()->getUsername().
                    "a Lister les chambre ","Chambres");
    
                    $data = $this->renderView('admin/chambre/adminindex.html.twig', [
                        "chambres"=>$chambres,
                        "types" => $types,
                        "entenes" => $entenes
                    ]);
                    $this->successResponse("Liste des chambres ", $link, $data);
                }else{
                    $types =$this->em->getRepository(Typechambre::class)->findAll();
                    $entene =$this->em->getRepository(Entene::class)->findAll();
    
                    $chambres=$this->em->getRepository(Chambre::class)->findBy(['entene' => $user->getAntene() ]);
                    $this->setlog("Liste des chambres","L'utilisateur ".$this->getUser()->getUsername().
                    "a Lister les chambre ","Chambres");
    
                    $data = $this->renderView('admin/chambre/index.html.twig', [
                        "chambres"=>$chambres,
                        "types" => $types,
                        "entene" => $entene
                    ]);
                    $this->successResponse("Liste des chambres ", $link, $data);
                }  
            }else{
                return $this->redirect('/login');
            }

               

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }



    /**
     * @Route("/dashchambre", name="dash-chambre", methods={"GET"})
     */
    public function dashchambre()
    {
        $link="dashchambre";

        try {
                $user = $this->getUser();
                if ($user) {
                   
                    $types =$this->em->getRepository(Typechambre::class)->findAll();
                    $entene =$this->em->getRepository(Entene::class)->findAll();
    
                    $chambres=$this->em->getRepository(Chambre::class)->findAll();
                    //dd($chambres);
                    $this->setlog("Dashboad Chambre","L'utilisateur ".$this->getUser()->getUsername().
                    " a vu le dashboad des chambres ","Chambre"); 
                     
                    $data = $this->renderView('admin/chambre/chambredashboard.html.twig', [
                        "chambres"=>$chambres,
                        "types" => $types,
                        "entene" => $entene
                    ]);
                    $this->successResponse("Liste des chambres ", $link, $data);
                }else{
                    return $this->redirect('/login');
                }


        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }


    /**
    * @Route("/chambrevue", name="chambrevue", methods={"GET"})
    */
    public function chambrevue()
    {
        $link="Chambrevue";
        try {
            $user = $this->getUser();
            if ($user) {                    
                $chambres=$this->em->getRepository(Chambre::class)->findAll();                        
                $data = $this->renderView('admin/chambre/index.html.twig', ["chambres"=>$chambres]);
                $this->successResponse("Liste des chambres ", $link, $data);
                
            }else{
                return $this->redirect('/login');
            }
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
    // dd($this->result);
        return $this->json($this->result);
    }

      
    /**
     * @Route("/chambreadd", name="chambre-add", methods={"POST"})
     */
    public function chambreadd(Request $request){
        $link="indexchambre";
        try {
            $user = $this->getUser();
            if ($user) {
               
                $type_id =  $request->get('type_id');
                $typech = $this->em->getRepository(Typechambre::class)->find($type_id);
    
                $entene_id = $request->get('entene_id');
                $entene = $this->em->getRepository(Entene::class)->find($entene_id);
        
    
                $numero = $request->get("numero");
                $surface = $request->get("surface");
                $nbenafant = $request->get("nbenafant");
                $nbaldulte = $request->get("nbaldulte");
                $nblit = $request->get("nblit");
                $description = $request->get("description");
                $photo1 = $request->files->get("file1");
                //dd($photo1);
                $photo2 = $request->files->get("file2");
                //dd($photo1);
                $photo3 = $request->files->get("file3");
                $photo4 = $request->files->get("file4"); 
                $existChambre = $this->em->getRepository(Chambre::class)->findOneBy(['numero'=>$numero, 'entene'=>$entene]);
                if($existChambre){
                    $this->log("Ce numero de chambre existe d??ja.", $link);
                    return $this->json($this->response);
                }
                $chambre = new Chambre();
    
                $chambre->setType($typech);
                
                $chambre->setEntene($entene);
    
                $chambre->setNumero($numero);
                $chambre->setSurface($surface);
                $chambre->setDescription($description);
                $chambre->setNbenafant($nbenafant);
                $chambre->setNbaldulte($nbaldulte);
                $chambre->setNblit($nblit);
                $chambre->setDescription($description);
    
                if(!empty($photo1) ){
                    $path1 = md5(uniqid()).'.'.$photo1->guessExtension();
                    $photo1->move($this->getParameter('Chambre'), $path1);    
                    $chambre->setPhoto1($path1);                    
                }else{
                    $chambre->setPhoto1("chambre.png");
                }
    
                if(!empty($photo2) ){
                    $path2 = md5(uniqid()).'.'.$photo2->guessExtension();
                    $photo2->move($this->getParameter('Chambre'), $path2);    
                    $chambre->setPhoto2($path2);                    
                }else{
                    $chambre->setPhoto1("chambre.png");
                }

                if(!empty($photo3)){
                    $path3 = md5(uniqid()).'.'.$photo3->guessExtension();
                    $photo3->move($this->getParameter('Chambre'), $path3);    
                    $chambre->setPhoto3($path3);                    
                }else{
                    $chambre->setPhoto1("chambre.png");
                }

                if(!empty($photo4)){
                    $path4 = md5(uniqid()).'.'.$photo4->guessExtension();
                    $photo4->move($this->getParameter('Chambre'), $path4);    
                    $chambre->setPhoto4($path4);                    
                }else{
                    $chambre->setPhoto1("chambre.png");
                }
                
                $this->em->persist($chambre);
                $this->em->flush();
                $this->setlog("AJOUT","L'utilisateur ".$this->getUser()->getUsername().
                " a ajouter la chambre ".$chambre->getNumero(),"CHAMBRE",$chambre->getId());
                //dd($chambre);
                $this->successResponse("chambre ajout??e ",$link);  
            }else {
                return $this->redirect('/login');
            }
   					
        			
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return new JsonResponse($this->result);
    
    }
   
    /**
     * @Route("deletecha", name="deletecha")
     */
    public function deleteAction(Request $request)
    {
        $link = "indexchambre";
        try{
            $user = $this->getUser();
            $id = intval($request->get('id'));
            if ($user) {               
                $chambre = $this->em->getRepository(Chambre::class)->find($id);                
                if (!$chambre) {
                    $this->log("Chambre introuvable", $link);
                }else{
                    if(count($chambre->getAllocations()) === 0 && count($chambre->getReservations()) === 0 ){
                        $this->em->remove($chambre);
                        $this->em->flush();
                        $this->setlog("SUPPRIMER","L'utilisateur ".$this->getUser()->getUsername().
                            " a Suprim?? la chambre ".$chambre->getNumero(),"Chambre N-",$chambre->getId());                             
                        $this->successResponse("Chambre supprim??e ", $link);
                    }else{
                        $this->log("Impossible de supprimer cette chambre, des informations y sont li??es.", $link);
                    }
                }
                
            }else {
                return $this->redirect('/login');
            }
        } 
        catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return new JsonResponse($this->result);
    }

           
     /**
      * @Route("/showchambre/{id}", name="showchambre")
      *
    */
    public function viewChambre($id) {
        $link="indexchambre";
        try {
        $user = $this->getUser();
        if ($user) {
            $chambre = $this->em->getRepository(Chambre::class)->find(intval($id));
            $reservations = $this->em->getRepository(Reservation::class)->findAll();
            if($chambre){            
                $recettejournalier = $this->em->getRepository(Allocation::class)->countaportAllocationparjourForroom($chambre);
                $recettemoisnalier = $this->em->getRepository(Allocation::class)->countaportAllocationparmoisForroom($chambre);
                $recetteglobale = $this->em->getRepository(Allocation::class)->montantAllocationChambre($chambre);
                $allocations = $this->em->getRepository(Allocation::class)->findBy(['chambre' => $chambre]);          
                $data = $this->renderView('admin/chambre/view.html.twig', [
                    'chambre' => $chambre,
                    'allocations'=>$allocations,
                    'reservations' => $reservations,
                    'recettejournaliere'=> $recettejournalier,
                    'recettemoisnalier'=>$recettemoisnalier,
                    'recetteglobale'=>$recetteglobale
                ]);
                $this->successResponse("Liste des chambres ", $link, $data);
            }else{
                $this->log("Chambre introuvable", $link);
            }
        } else {
            return $this->redirect('/login');
        }
        
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
       return $this->json($this->result);
       


    }  
    
    
    /**
      * @Route("chambreedit/{id}/edit", name="chambre-edit")
      *
      */
      public function chambreedit($id) {
          
        $link="indexchambre";
        try {
            $user = $this->getUser();
            if ($user) {
                $chambre = $this->em->getRepository(Chambre::class)->find($id);
                $entenes = $this->em->getRepository(Entene::class)->findAll();
                $types =$this->em->getRepository(Typechambre::class)->findAll();
            
            $data = $this->renderView('admin/chambre/edit.html.twig', [
                'chambre' => $chambre,
                'types' => $types,
                'entenes' => $entenes,
                
    
               
            ]);
            $this->successResponse("vue edit des chambres ", $link, $data);
            } else {
                return $this->redirect('/login');
            }
            
          
   
    
    } catch (\Exception $ex) {
        $this->log($ex->getMessage(), $link);
    }
    return $this->json($this->result);
        
        
    }


    /**
      * @Route("editrchambre/edit", name="edit-rchambre")
      *
      */
      public function editrchambre(Request $request)
       {
          
        $link="indexchambre";
        try {
            $user = $this->getUser();

            if ( $user) {
                $id = $request->get("id");
                $numero = $request->get("numero");
                $typeId = intval($request->get("typeId"));
                $surface = $request->get("surface");
                $nbenafant = $request->get("nbenafant");
                $nbaldulte = $request->get("nbaldulte");
                $nblit = $request->get("nblit");
                $description = $request->get("description");

                $photo1 = $request->files->get("file1");
                $photo2 = $request->files->get("file2");
                $photo3 = $request->files->get("file3");
                $photo4 = $request->files->get("file4");

                $chambre = $this->em->getRepository(Chambre::class)->find($id);
                $typechambre = $this->em->getRepository(Typechambre::class)->find($typeId);
                $existChambre = $this->em->getRepository(Chambre::class)->findOneBy(['numero'=>$numero, 'entene'=>$chambre->getEntene()]);
                if($existChambre && $existChambre->getId() != $chambre->getId()){
                    $this->log("Ce numero de chambre existe d??ja.", $link);
                    return $this->json($this->response);
                }

                $chambre->setNumero($numero);
                $chambre->setType($typechambre);
                $chambre->setSurface($surface);
                $chambre->setDescription($description);
                $chambre->setNbenafant($nbenafant);
                $chambre->setNbaldulte($nbaldulte);
                $chambre->setNblit($nblit);
                $chambre->setDescription($description);
                if(!empty($photo1) ){
                    $path1 = md5(uniqid()).'.'.$photo1->guessExtension();
                    $photo1->move($this->getParameter('Chambre'), $path1);
                    $chambre->setPhoto1($path1);                    
                }

                if(!empty($photo2) ){
                    $path2 = md5(uniqid()).'.'.$photo2->guessExtension();
                    $photo2->move($this->getParameter('Chambre'), $path2);
                    $chambre->setPhoto2($path2);                    
                }
                if(!empty($photo3)){
                    $path3 = md5(uniqid()).'.'.$photo3->guessExtension();
                    $photo3->move($this->getParameter('Chambre'), $path3);
                    $chambre->setPhoto3($path3);                    
                }
            if(!empty($photo4)){
                $path4 = md5(uniqid()).'.'.$photo4->guessExtension();
                $photo4->move($this->getParameter('Chambre'), $path4);
                $chambre->setPhoto4($path4);                    
            }                

            $this->em->persist($chambre);
            $this->em->flush($chambre);
            $this->setlog("Modifier","L'utilisateur ".$this->getUser()->getUsername().
                " a Modifi?? la chambre ".$chambre->getNumero(),"CHAMBRE",$chambre->getId());

            $this->successResponse("chambre Modifi??e !", $link);  
            } else {
                return $this->redirect('/login');
            }
            
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);

        
        
    }

    /**
     * @Route("/filter", name="filter", methods={"GET"})
     */
    public function filter(Request $request)
    {
        $link="indexchambre";
        try {   
            $user = $this->getUser();
            if ($user) {
                $param = $request->get("param");
                $val = $request->get("valu");
                $valu = str_replace(' ', '', $val);
                $chambres = [] ;
                //dd($param);
                if($param == 'entene'){
                    $qb = $this->em->createQueryBuilder();
                        $qb->select('c')
                            ->from(Chambre::class, 'c')
                            ->where("c.".$param."= '".$valu."'");
                        $query = $qb->getQuery();
                    $chambres = $query->execute();
                }elseif($param == 'type'){
                    $qb = $this->em->createQueryBuilder();
                        $qb->select('c')
                            ->from(Chambre::class, 'c')
                            ->where("c.".$param."= '".$valu."'");
                        $query = $qb->getQuery();
                    $chambres = $query->execute();
                }else{
                    $qb = $this->em->createQueryBuilder();
                        $qb->select('c')
                            ->from(Chambre::class, 'c')
                            ->where("c.".$param."= '".$valu."'");
                        $query = $qb->getQuery();
                    $chambres = $query->execute();
                   
    
                }
    
            
                $types =$this->em->getRepository(Typechambre::class)->findAll();
                $entene =$this->em->getRepository(Entene::class)->findAll();
                //dd($chambres);
                $data = $this->renderView('admin/chambre/index.html.twig', [
                    "chambres"=>$chambres,
                    "types" => $types,
                    "entene" => $entene
                ]);
                $this->successResponse("Liste des chambres ", $link, $data);
            } else {
                return $this->redirect('/login');
            }
             
           

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }

    /**
     * @Route("/printbed", name="print-bed")
     */
    public function printbed(){
        $link="indexchambre";
            $antene = $this->getUser()->getAntene();
            $chambres=$this->em->getRepository(Chambre::class)->findAll();
            //dd($antene);
            $template = $this->renderView('admin/print/printbed.pdf.twig', [
                "chambres" => $chambres,
                "antene" => $antene,
            ]);
        
       // dd($this->result);
       return $this->returnPDFResponseFromHTML($template, "Liste des Chambres"); 
    }

}