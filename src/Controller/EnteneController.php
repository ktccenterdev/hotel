<?php

namespace App\Controller;

use App\Controller\DefaultController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EnteneRepository;
use App\Entity\Entene;
use Symfony\Component\HttpFoundation\JsonResponse;




class EnteneController extends DefaultController
{
    
    /**
     * @Route("/test", name="test", methods={"GET"})
     */
    public function test()
    {
        $link="test";

        try {
                $data = $this->renderView('admin/clients/index.html.twig', [ ]);
                $this->successResponse("Liste des clients ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }
    
       /**
     * @Route("/entene", name="entene", methods={"GET"})
     */
    public function entene()
    {
        $link="entene";
        

        try {
                $entenes=$this->em->getRepository(Entene::class)->findAll();
                //dd($entenes);
                $data = $this->renderView('admin/entene/index.html.twig', ["entenes"=>$entenes]);
                $this->successResponse("Liste des entenes ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }

      
    /**
     * @Route("/create", name="enteneadd", methods={"POST"} )
     */
    public function create(Request $request){
        $link="entene";

        try {
        
            $nom = $request->get("nom");
            $localisation = $request->get("localisation");
            $description = $request->get("description");
            $tel = $request->get("tel");
            $email = $request->get("email");
            $bp = $request->get("bp");
            $site = $request->get("site");
            $logo = $request->files->get("logo");
            //dd($logo );

            $name = explode(" ", $nom);
            $acronym = "";

            foreach ($name as $w) {
                $acronym .= $w[0];
            }
            $acronyms = strtolower($acronym);
            $entene = new Entene();

            $entene->setNom($nom);
            $entene->setAcronym($acronyms);
            $entene->setLocalisation($localisation);
            $entene->setDescription($description);
            $entene->setTel($tel);
            $entene->setEmail($email);
            $entene->setBp($bp);
            $entene->setSite($site);
            if(!empty($logo)){
                $path = md5(uniqid()).'.'.$logo->guessExtension();
                //dd($path);
                $logo->move($this->getParameter('Entene'), $path);

                $entene->setLogo($path);
                $entene->setPhoto($logo->getClientOriginalName());
            }
        
        
            $this->em->persist($entene);
            $this->em->flush();
            $this->setlog("AJOUTER","L'utilisateur ".$this->getUser()->getUsername().
            " a ajouter l'entene ".$entene->getNom(),"ENTENE",$entene->getId());
            $this->successResponse("Antene ajoutée ","entene");  
            }catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link,"entene");
            }
        return new JsonResponse($this->result); 
    }

    /**
      * @Route("delete/{id}", name="antne-delete")
      *
      */
    public function deleteAction($id) {
        $link="entene";
          try{
        $em = $this->getDoctrine()->getManager();
        $entene = $this->getDoctrine()->getRepository(Entene::class);
        $entene = $entene->find($id);
        if (!$entene) {
            throw $this->createNotFoundException(
                'There are no articles with the following id: ' . $id
            );
        }
        $em->remove($entene);
        $em->flush();
        $this->setlog("SUPPRIMER","L'utilisateur ".$this->getUser()->getUsername().
        " a supprimer l'entene ".$entene->getNom(),"ENTENE",$entene->getId());
        $this->successResponse("Antene supprimé ","entene");
    } 
    catch (\Exception $ex) {
        $this->log($ex->getMessage(),$link, "entene");
    } 
    return new JsonResponse($this->result);
      
       // return new JsonResponse($this->result);


    }


    /**
      * @Route("/show/{id}", name="show")
      *
      */
      public function viewAction($id) {
      
        $link="vueentene";

        try {
            $entene = $this->getDoctrine()->getRepository(Entene::class)->find($id);
       
            if (!$entene) {
                throw $this->createNotFoundException(
                    'Aucun entene pour l\'id: ' . $id
                );
            }
            $data = $this->renderView('admin/entene/view.html.twig', [
                'entene' => $entene
               
            ]);
            $this->successResponse("vueentene ", $link, $data);


        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return $this->json($this->result);
      
    }



    /**
      * @Route("/edit/{id}", name="entene_edit")
      *
    */
      public function updateAction($id) {
      
        $link="editeentene";

        try {
            $entene = $this->getDoctrine()->getRepository(Entene::class)->find($id);
       
            if (!$entene) {
                throw $this->createNotFoundException(
                    'Aucun entene pour l\'id: ' . $id
                );
            }
            $data = $this->renderView('admin/entene/edite.html.twig', [
                'entene' => $entene
               
            ]);
            $this->successResponse("editeentene ", $link, $data);


        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return $this->json($this->result);
      
    }


    /**
      * @Route("/editer/anteme", name="editerantene")
      *
    */
    public function editerantene(Request $request) {
      
        $link="entene";
        
        try {
            $nom = $request->get("nom");
            $id = $request->get("id");
            $localisation = $request->get("localisation");
            $description = $request->get("description");
            $tel = $request->get("tel");
            $email = $request->get("email");
            $bp = $request->get("bp");
            $site = $request->get("site");
            $logo = $request->files->get("logo");
            
            $entene = $this->getDoctrine()->getRepository(Entene::class)->find($id);
       
            if (!$entene) {
                throw $this->createNotFoundException(
                    'Aucun entene pour l\'id: ' . $id
                );
            }
            $entene->setNom($nom);
            $entene->setLocalisation($localisation);
            $entene->setDescription($description);
            $entene->setTel($tel);
            $entene->setEmail($email);
            $entene->setBp($bp);
            $entene->setSite($site);
            if(!empty($logo)){
                $path = md5(uniqid()).'.'.$logo->guessExtension();
                $logo->move($this->getParameter('Entene'), $path);
    
                $entene->setLogo($path);
            }
            /*$data = $this->renderView('admin/entene/edite.html.twig', [
                'entene' => $entene
               
            ]);*/

            $this->em->persist($entene);
            $this->em->flush();
            $this->setlog("Modifier","Antène ".$this->getUser()->getUsername().
            " a modifier l'entene ".$entene->getNom(),"ENTENE",$entene->getId());
            $this->successResponse("Antene Modifier ","entene");  

            //$this->successResponse("editeentene ", $link, $data);


        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link,"entene");
        } 
        return $this->json($this->result);
      
    }


	   
    /**
     * @Route("/printantene", name="print-antene")
     */
    public function printantene(){
        $link="Entene";

            $antene=$this->em->getRepository(Entene::class)->findAll();
            //dd($antene);
            $hotel['nom'] = "Hotel RAPHIA";
            $hotel['localisation'] = "Yaoundé Cameroun";
            $hotel['bp'] = "55";
            $hotel['tel'] = "+237 675887774";
            $hotel['email'] = "info@hotel.com";
            $hotel['site'] = "https://syshotel.ktc-center.net";
            //dd($hotel);

            $template = $this->renderView('admin/print/printantene.pdf.twig', [
                "antenes" => $antene,
                "antene" => $hotel
            ]);
        
       // dd($this->result);
       return $this->returnPDFResponseFromHTML($template, "Liste des antenes", "L"); 
    }  
}
