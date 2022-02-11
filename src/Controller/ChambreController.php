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
                return $this->redirectToRoute('login');
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
                    return $this->redirectToRoute('login');
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
                    //dd($entenes);
                    $this->setlog("visualisation des chambres","L'utilisateur ".$this->getUser()->getUsername().
                    "a vu les chambres","EMPLOYEE"); 
                        
                    $data = $this->renderView('admin/chambre/index.html.twig', ["chambres"=>$chambres]);
                    $this->successResponse("Liste des chambre ", $link, $data);
                   
                }else{
                    return $this->redirectToRoute('login');
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
                $this->em->flush();
                $this->setlog("AJOUT","L'utilisateur ".$this->getUser()->getUsername().
                " a ajouter la chambre ".$chambre->getNumero(),"CHAMBRE",$chambre->getId());
                //dd($chambre);
                $this->successResponse("chambre ajoutée ",$link);  
            }else {
                return $this->redirectToRoute('login');
            }
   					
        			
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return new JsonResponse($this->result);
    
    }
   
    /**
     * @Route("deletecha/{id}", name="deletecha")
     */
    public function deleteAction($id)
    {
        $link = "deletecha";
        try{
            $user = $this->getUser();
            if ($user) {
               
                $chambre = $this->em->getRepository(Chambre::class)->find($id);
                if (!$chambre) {
                    throw $this->createNotFoundException(
                        'There are no articles with the following id: ' . $id
                    );
                }
                $this->em->remove($chambre);
                $this->em->flush();
                $this->setlog("SUPPRIMER","L'utilisateur ".$this->getUser()->getUsername().
                    " a Suprimer la chambre ".$chambre->getNumero(),"Chambre N-",$chambre->getId()); 
                     
                $this->successResponse("Antene supprimé ", $link);
            }else {
                return $this->redirectToRoute('login');
            }
        } 
        catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return new JsonResponse($this->result);
        
        // return new JsonResponse($this->result);

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
            $chambre = $this->em->getRepository(Chambre::class)->find($id);
            $reservations = $this->em->getRepository(Reservation::class)->findAll();
                
            $recettejournalier = $this->em->getRepository(Allocation::class)->countaportAllocationparjourForroom($chambre);
            $recettemoisnalier = $this->em->getRepository(Allocation::class)->countaportAllocationparmoisForroom($chambre);
            if(empty($recettejournalier[1])){
                $recettejournalier[1]=0;   
            }
            if(empty($recettemoisnalier[1])){
                $recettemoisnalier[1]=0;   
            }
            $allocations = $this->em->getRepository(Allocation::class)->findBy(['chambre' => $chambre]);
                if (!$chambre) {
                    throw $this->createNotFoundException(
                        'Aucun chambre pour l\'id: ' . $id
                );
            }
            
            $data = $this->renderView('admin/chambre/view.html.twig', [
                'chambre' => $chambre,
                'allocations'=>$allocations,
                'reservations' => $reservations,
                'recettejournaliere'=>$recettejournalier[1],
                'recettemoisnalier'=>$recettemoisnalier[1]
               
               
            ]);
            $this->successResponse("Liste des chambres ", $link, $data);
        } else {
            return $this->redirectToRoute('login');
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
                return $this->redirectToRoute('login');
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
      public function editrchambre(Request $request): Response
       {
          
        $link="indexchambre";
        try {
            $user = $this->getUser();

            if ( $user) {
                $id = $request->get("id");
                $numero = $request->get("numero");
                $surface = $request->get("surface");
                $nbenafant = $request->get("nbenafant");
                $nbaldulte = $request->get("nbaldulte");
                $nblit = $request->get("nblit");
                $description = $request->get("description");

                $entene_id = $request->get("entene_id");
                $entene = $this->em->getRepository(Entene::class)->find($entene_id);
                //dd($entene);

                $photo1 = $request->files->get("file1");
                $photo2 = $request->files->get("file2");
                $photo3 = $request->files->get("file3");
                $photo4 = $request->files->get("file4");




                $chambre = $this->em->getRepository(Chambre::class)->find($id);

                $chambre->setNumero($numero);
                $chambre->setSurface($surface);
                $chambre->setDescription($description);
                $chambre->setNbenafant($nbenafant);
                $chambre->setNbaldulte($nbaldulte);
                $chambre->setNblit($nblit);
                $chambre->setEntene($entene);
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
            $this ->addFlash('success','chambre modifié avec succes');
            $this->setlog("Modifier","L'utilisateur ".$this->getUser()->getUsername().
                " a Modifier la chambre ".$chambre->getNumero(),"CHAMBRE",$chambre->getId());

            $this->successResponse("chambre Modifier !", $link);  
            } else {
                return $this->redirectToRoute('login');
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
                return $this->redirectToRoute('login');
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