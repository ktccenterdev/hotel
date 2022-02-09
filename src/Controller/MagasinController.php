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

        try {
			if($this->getUser()->getIsadmin()){
                $magasins = $this->em->getRepository(Magasin::class)->findAll();
            }else{
                $magasins = $this->em->getRepository(Magasin::class)->findBy([
                    "antene" => $this->getUser()->getAntene()
                ]);
            }
            // dd($magasins);
            $entene = $this->em->getRepository(Entene::class)->findAll();
            $data = $this->renderView('admin/magasin/index.html.twig', [
                "magasins" => $magasins,
                "entene" => $entene,
            ]);
            $this->successResponse("Liste des magasins ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }

    /**
     * @Route("/addmagasin", name="magasin-add", methods={"POST"})
     */
    public function addmagasin(Request $request)
    {
        try {
            $nom =  $request->get('nom');
            $type =  $request->get('type');
            $description =  $request->get('description');
            $antene =  $request->get('entene_id');
            $entene =$this->em->getRepository(Entene::class)->find($antene);

            $magasin= new Magasin();
            $magasin->setNom($nom);
            $magasin->setType($type);
            $magasin->setDescription($description);
            $magasin->setAntene($entene);
            
            $this->em->persist($magasin);
            $this->em->flush();
            $this->setlog("AJOUTER","Le Magasin ".$this->getUser()->getUsername().
            " a ajouter le Magasin ".$magasin->getNom(),"MAGASIN",$magasin->getId());
            $this->successResponse("Magasin ajouter !","index-magasin");  
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), "index-magasin");
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
     * @Route("/detail/{id}", name="detail-magasin", methods={"GET"})
     */
    public function showmagasin($id){
        $link="detail-magasin";

        try {
            $magasin = $this->em->getRepository(Magasin::class)->find($id);
            
            //dd($magasin);
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




}