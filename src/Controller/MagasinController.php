<?php

namespace App\Controller;

use App\Entity\Magasin;
use App\Entity\Entene;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Entrestock;
use App\Entity\Entreitem;
use App\Entity\Produit;
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
                $data = $this->renderView('admin/magasin/index.html.twig', [
                    "magasins" => $magasins,
                    "entenes" => $this->getAllAntennes()
                ]);
                $this->successResponse("Liste des magasins ", $link, $data);
            }else{
                return $this->redirect('/login');
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
        $type = "Interne";
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
                            $type = "Général";
                        }
                        $magasin->setType($type);
                        $magasin->setAntene($antenne);
                        $magasin->setNom($nom);
                        $magasin->setDescription($description);
                        $this->em->persist($magasin);
                        $this->em->flush($magasin);
                        $this->setlog("AJOUTER","Le Magasin ".$this->getUser()->getUsername().
                        " a ajouter le Magasin ".$magasin->getNom(),"MAGASIN",$magasin->getId());
                        $this->successResponse("Magasin ajouter !",$link);  
                    }else{
                        $this->log("Antenne introuvable", $link);
                    }
                }else{
                    $this->log("Veuillez sélectionner une antenne", $link);
                }
                
            }else{
                $this->log("Le nom est obligatoire", $link);
            }
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
        $link="listmagasin";
        try {
            if($id){
                $magasin = $this->em->getRepository(Magasin::class)->find($id);
                if($magasin){
                    $entrestocks = $this->em->getRepository(Entrestock::class)->findBy(['magasin' => $magasin]);
                    $antenne = $this->em->getRepository(Entene::class)->find($magasin->getAntene());
                    $data = $this->renderView('admin/magasin/listeentree.html.twig', [
                        "magasin" => $magasin,
                        "entene" => $antenne,
                        "entrestocks" => $entrestocks
                    ]);
                    $this->successResponse("Liste des magasins ", $link, $data);

                }else{
                    $this->log("Magasin introuvable", $link);
                }
            }else{
                $this->log("Veuillez sélectionner un magasin", $link);
            }            
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);        
    }

    /**
     * @Route("/indexlissortit/{id}", name="index-lissortit", methods={"GET"})
     */
    public function indexListeSortie($id)
    {
        $link="listmagasin";
        try {
            $user = $this->getUser();
            if($user){
                $antenne = $this->getUser()->getAntene();
                $magasin = $this->em->getRepository(Magasin::class)->find($id);
                if($user->getIsadmin()){
                    $antennes = $this->getAllAntennes();
                }else{
                    $antennes = [$antenne];
                }
                $sortirs = $this->em->getRepository(Sortiritem::class)->findBy(['sortistock' => $magasin]);
                $data = $this->renderView('admin/magasin/listesortit.html.twig', [
                    "magasins" => $magasin,
                    "entene" => $antennes,
                    "sortirs" => $sortirs
                ]);
                $this->successResponse("Liste des sorties ", $link, $data);
            }else{
                return $this->redirect('/login');
            }
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
        
    }


	/**
     * @Route("/printmagasin", name="print-magasin")
     */
    public function printmagasin(){
        $user = $this->getUser();
        $magasins = array();
        if($user){
            $antenne = $this->getUser()->getAntene();
            if($user->getIsadmin()){
                $magasins = $this->em->getRepository(Magasin::class)->findAll();
            }else{
                if($antenne->getMagasin()){
                     $magasins = $this->em->getRepository(Magasin::class)->findBy(['antene'=>$antenne->getMagasin()]);
                }
            }
            $template = $this->renderView('admin/print/printmagasin.pdf.twig', [
                "magasins" => $magasins,
                "antene" => $antenne,
            ]);
        }else{
            return $this->redirectToRoute("login");
        }
        return $this->returnPDFResponseFromHTML($template, "Liste des magasins"); 
    }

    /**
     * @Route("/detailmagasin/{id}", name="detail-magasin", methods={"GET"})
     */
    public function showmagasin($id){
        $link="listmagasin";
        $user = $this->getUser();
        try {
            if($id){
                $magasin = $this->em->getRepository(Magasin::class)->find($id);
                $produits = $this->em->getRepository(Produit::class)->findAll();
                if($magasin){
                    $data = $this->renderView('admin/magasin/showmagasin.html.twig', [
                        "magasin" => $magasin,
                        "produits" => $produits
                    ]);
                    $this->successResponse("Détails d'un magasin ", $link, $data);
                }else{
                    $this->log("Magasin introuvable", $link);
                }
            }else{
                $this->log("Veuillez sélectionner un magasin", $link);
            }        
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }

    /**
     * @Route("/editmagasin/{id}", name="edit-magasin", methods={"GET"})
     */
    public function editmagasin($id){
        $link="listmagasin";
        $user = $this->getUser();
        if($user){
            try {
                $magasin = $this->em->getRepository(Magasin::class)->find($id);
                
                $data = $this->renderView('admin/magasin/editmagasin.html.twig', [
                    "magasin" => $magasin
                ]);
                $this->successResponse("Edition d'un magasin ", $link, $data);
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirect('/login');
        }
    }

    /**
     * @Route("/editionmagasin", name="edition-magasin", methods={"POST"})
     */
    public function editionmagasin(Request $request){
        $link="listmagasin";
        if($this->getUser()){
            try {
                $id =  intval($request->get('id'));
                if($id){
                    $magasin = $this->em->getRepository(Magasin::class)->find($id);                
                    if($magasin){               
                        $nom =  $request->get('nom');
                        $description =  $request->get('description');                    
                        if(!empty($nom)){
                            $magasin->setNom($nom);
                            $magasin->setNom($description);
                            $this->em->persist($magasin);
                            $this->em->flush();
                            $this->setlog("Modification",$this->getUser()->getUsername().
                            " a modifié le magasin ".$magasin->getNom(),"MAGASIN",$magasin->getId());
                            $this->successResponse("Magasin Modifié !",$link);
                        }else{
                            $this->log("Le nom est obligatoire", $link);
                        }  
                    }else{
                        $this->log("Magasin introuvable", $link);
                    }          
                }else{
                    $this->log("Veuillez sélectionner un magasin", $link);
                }

            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirect('/login');
        }
    }

    /**
    * @Route("deletemagasin", name="delete-magasin",methods={"DELETE"})
    */
   /* public function deletemagasin(Request $request) {
        $link="listmagasin";

        if($this->getUser()){            
            try{
                $id =  intval($request->get('id'));
                if($id){
                    $magasin = $this->em->getRepository(Magasin::class)->find($id);                
                    if($magasin){  
                        $magasin = $this->getDoctrine()->getRepository(Magasin::class)->find($id);             
                        if(!is_null($magasin)){                            
                            if(count($magasin->getEntrestocks()) === 0 && count($magasin->getSortirstocks())===0){
                                $this->em->remove($magasin);
                                $this->em->flush();
                                $this->setlog("SUPPRESION",$this->getUser()->getUsername().
                                    " a supprimé le Magasin ".$magasin->getNom(),"MAGASIN",$magasin->getId());
                                $this->successResponse("Magasin Supprimé ",$link);
                            }else{
                                $this->log("Impossible de supprimer ce magasin, Car il est lié à d'autres ressources.", $link);
                            }
                        
                        }else{
                            $this->log("ce magasin semble ne pas exister", $link);
                        }
                    }else{
                        $this->log("Magasin introuvable", $link);
                    }
                }else{
                    $this->log("Veuillez sélectionner un magasin", $link);
                }
            }
            catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            } 
            return new JsonResponse($this->result);            
        }else{
            return $this->redirect('/login');
        }
    }*/


}