<?php

namespace App\Controller;

use App\Entity\Magasin;
use App\Entity\Entene;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Entrestock;
use App\Entity\Entreitem;
use App\Entity\Sortiritem;
use App\Entity\Sortirstock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MagasinController extends DefaultController
{


    /**
     * @Route("/listmagasin", name="index-magasin", methods={"GET"})
     */
    public function listmagasin()
    {
        $link="listmagasin";
        $user = $this->getUser();
       
        try {
            if($user){
                if($user->getIsadmin()){
                    $magasins = $this->em->getRepository(Magasin::class)->findAll();
                }else{
                    $magasins = $this->em->getRepository(Magasin::class)->findBy([
                        "antene" => $this->getUser()->getAntene()
                    ]);
                }
                $entene = $this->em->getRepository(Entene::class)->findAll();
                $data = $this->renderView('admin/magasin/index.html.twig', [
                    "magasins" => $magasins,
                    "entene" => $entene,
                ]);
                $this->successResponse("Liste des magasins ", $link, $data);
            }else{
                return $this->redirectToRoute('login');
            }

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }

    /**
     * @Route("/addmagasin", name="magasin-add", methods={"POST"})
     */
    public function addmagasin(Request $request)
    {
        $link="listmagasin";
        $type = "Autre";
        try {
            $nom =  $request->get('nom');
            if($nom){
                $description =  $request->get('description');
                $antenneId =  intval($request->get('entene_id'));
                if($antenneId){
                    $antenne =$this->em->getRepository(Entene::class)->find($antenneId);
                    if(!is_null($antenne)){
                        $magasin = new Magasin();
                        if($antenne->getIsprincipal()){
                            $type = "GENERAL";
                        }
                        $magasin->setType($type);
                        $magasin->setAntene($antenne);
                        $magasin->setNom($nom);
                        $magasin->setDescription($description);
                        $this->em->flush($magasin);
                    }else{
                        $this->log("Antenne introuvable", $link);
                    }
                }else{
                    $this->log("Veuillez sélectionner une antenne", $link);
                }
                
            }else{
                $this->log("Le nom est obligatoire", $link);
            }
            
            

            $magasin= new Magasin();
            $magasin->setNom($nom);
            $magasin->setType($type);
            $magasin->setDescription($description);
            $magasin->setAntene($entene);
            
            $this->em->persist($magasin);
            $this->em->flush();
            $this->setlog("AJOUTER","Le Magasin ".$this->getUser()->getUsername().
            " a ajouter le Magasin ".$magasin->getNom(),"MAGASIN",$magasin->getId());
            $this->successResponse("Magasin ajouter !",$link);  
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }


    /**
     * @Route("/indexlisentree/{id}", name="index-lisentree", methods={"GET"})
     */
    public function lisentree($id)
    {
        $link="lisentree";

        try {
            $magasins = $this->em->getRepository(Magasin::class)->find($id);
            $entrestocks = $this->em->getRepository(Entrestock::class)->findBy(['magasin' => $magasins]);
            $entene = $this->em->getRepository(Entene::class)->findAll();
            $data = $this->renderView('admin/magasin/listeentree.html.twig', [
                "magasins" => $magasins,
                "entene" => $entene,
                "entrestocks" => $entrestocks
            ]);
            $this->successResponse("Liste des magasins ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
        
    }



    /**
     * @Route("/indexlissortit/{id}", name="index-lissortit", methods={"GET"})
     */
    public function indexlissortit($id)
    {
        $link="lisentree";

        try {
            $magasins = $this->em->getRepository(Magasin::class)->find($id);
            $entene = $this->em->getRepository(Entene::class)->findAll();
            $sortirs = $this->em->getRepository(Sortiritem::class)->findBy(['sortistock' => $magasins]);
            $data = $this->renderView('admin/magasin/listesortit.html.twig', [
                "magasins" => $magasins,
                "entene" => $entene,
                "sortirs" => $sortirs
            ]);
            $this->successResponse("Liste des magasins ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
        
    }


	/**
     * @Route("/printmagasin", name="print-magasin")
     */
    public function printmagasin(){
        $antene = $this->getUser()->getAntene();
        $magasins = $this->em->getRepository(Magasin::class)->findAll();
        
        //dd($antene);
        $template = $this->renderView('admin/print/printmagasin.pdf.twig', [
            "magasins" => $magasins,
            "antene" => $antene,
        ]);
        
       // dd($this->result);
       return $this->returnPDFResponseFromHTML($template, "Liste des magasins"); 
    }

    /**
     * @Route("/detailmagasin/{id}", name="detail-magasin", methods={"GET"})
     */
    public function showmagasin($id){
        $link="detail-magasin";

        try {
            $magasin = $this->em->getRepository(Magasin::class)->find($id);
            
            $data = $this->renderView('admin/magasin/showmagasin.html.twig', [
                "magasin" => $magasin
            ]);
            $this->successResponse("Détail d'un magasins ", $link, $data);
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }

    /**
     * @Route("/editmagasin/{id}", name="edit-magasin", methods={"GET"})
     */
    public function editmagasin($id){
        $link="edit-magasin";

        try {
            $magasin = $this->em->getRepository(Magasin::class)->find($id);
            
            $data = $this->renderView('admin/magasin/editmagasin.html.twig', [
                "magasin" => $magasin
            ]);
            $this->successResponse("edition d'un magasins ", $link, $data);
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);

    }

    /**
     * @Route("/editionmagasin", name="edition-magasin", methods={"POST"})
     */
    public function editionmagasin(Request $request){
        $link="listmagasin";

        try {
            $id =  $request->get('id');

            $nom =  $request->get('nom');
            $type =  $request->get('type');
            $description =  $request->get('description');

            $magasin = $this->em->getRepository(Magasin::class)->find($id);
            if(!empty($nom)){
                $magasin->setNom($nom);
            }
            if(!empty($type)){
                $magasin->setType($type);
            }
            if(!empty($description)){
                $magasin->setDescription($description);
            }
            
            $this->em->persist($magasin);
            $this->em->flush();
            $this->setlog("Modification",$this->getUser()->getUsername().
            " a modifier le Magasin ".$magasin->getNom(),"MAGASIN",$magasin->getId());
            $this->successResponse("Magasin Modifié !",$link);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);

    }

    /**
    * @Route("deletemagasin", name="delete-magasin",methods={"DELETE"})
    */
    public function deletemagasin(Request $request) {
        $link="listmagasin";
        //dd('bnnn');
        try{
            $id =  $request->get('id');
            //dd($id);
            $magasin = $this->getDoctrine()->getRepository(Magasin::class)->find($id);
            //dd($magasin);
            if(!is_null($magasin)){
                if(count($magasin->getEntrestocks()) === 0 && count($magasin->getSortirstocks())===0){
                    //$this->em->remove($magasin);
                    //$this->em->flush();
                    dd($magasin);
                    $this->setlog("SUPPRESION",$this->getUser()->getUsername().
                        " a supprimé le Magasin ".$magasin->getNom(),"MAGASIN",$magasin->getId());
                    $this->successResponse("Magasin Supprimé ",$link);
                }else{
                    $this->log("Impossible de supprimer ce magasin, Car il est lié à d'autres ressources.", $link);
                }
             
            }else{
                $this->log("ce magasin semble ne pas exister", $link);
            }

        }
        catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return new JsonResponse($this->result);
          
    }


}