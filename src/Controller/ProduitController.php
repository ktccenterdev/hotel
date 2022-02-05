<?php

namespace App\Controller;

use App\Entity\Entene;
use App\Entity\Produit;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EnteneRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;



class ProduitController extends DefaultController
{


    /**
     * @Route("/listproduit", name="index-produit", methods={"GET"})
     */
    public function listproduit()
    {
        $link="listproduit";

        try {
            $produits = $this->em->getRepository(Produit::class)->findAll();
           
            //dd($produits);
            $data = $this->renderView('admin/produit/index.html.twig', [
                "produits" => $produits,
            ]);
            $this->successResponse("Liste des produits ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }

    /**
     * @Route("/addproduit", name="produit-add", methods={"POST"})
     */
    public function addproduit(Request $request)
    {
        $link = "listproduit";
        try {
            $nom =  $request->get('nom');
            $type =  $request->get('type');
            $description =  $request->get('description');
            $qtseuil =  $request->get('qtseuil');
            $photo = $request->files->get("photo");
            $produit= new Produit();
            $produit->setNom($nom);
            $produit->setType($type);
            $produit->setDescription($description);
            $produit->setQtseuil($qtseuil);
            if(!empty($photo) ){
                $path = md5(uniqid()).'.'.$photo->guessExtension();
                $photo->move($this->getParameter('Produits'), $path);
                $produit->setPhoto($path);                
            }else{
                $produit->setPhoto("produit.png");
            }
            
            $this->em->persist($produit);
            $this->em->flush();
            $this->setlog("AJOUTER","Le Produit ".$this->getUser()->getUsername().
            " a ajouter le Produit ".$produit->getNom(),"PRODUIT",$produit->getId());
            $this->successResponse("Produit ajouté ",$link);  
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }

    /**
     * @Route("/detailproduit/{id}", name="detail-produit", methods={"GET"})
     */
    public function detailproduit($id)
    {
        $link="detailproduit";

        try {
            $produit = $this->em->getRepository(Produit::class)->find($id);
           
            // dd($produit);
            $data = $this->renderView('admin/produit/detail.html.twig', [
                "produit" => $produit,
            ]);
            $this->successResponse("detail du produit ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }

    /**
     * @Route("/editerproduit/{id}", name="editer-produit", methods={"GET"})
     */
    public function editerproduit($id)
    {
        $link="editerproduit";

        try {
            $produit = $this->em->getRepository(Produit::class)->find($id);
           
            // dd($produit);
            $data = $this->renderView('admin/produit/editer.html.twig', [
                "produit" => $produit,
            ]);
            $this->successResponse("detail du produit ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }

    /**
     * @Route("/editproduit", name="produit-edit", methods={"POST"})
     */
    public function saveproduit(Request $request)
    {
        $link="produit";

        try {
            $id =  $request->get('id');
            $nom =  $request->get('nom');
            $type =  $request->get('type');
            $description =  $request->get('description');
            $qtseuil =  $request->get('qtseuil');
            $photo = $request->files->get("photo");
            //dd($qtseuil);

            $produit = $this->em->getRepository(Produit::class)->find($id);
            $produit->setNom($nom);
            $produit->setType($type);
            $produit->setDescription($description);
            $produit->setQtseuil($qtseuil);
            if(!empty($photo) ){
                $path = md5(uniqid()).'.'.$photo->guessExtension();
                $photo->move($this->getParameter('Produits'), $path);

                $produit->setPhoto($path);
                
            }
            $this->em->persist($produit);
            $this->em->flush();
            $this->setlog("MODIFIER","Le Produit ".$this->getUser()->getUsername().
            " a Modifier le Produit ".$produit->getNom(),"PRODUIT",$produit->getId());
            $this->successResponse("Produit Modifié !", "index-produit");  
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), "index-produit");
        }
        return new JsonResponse($this->result);
    }

    /**
     * @Route("/printproduit", name="print-produit")
     */
    public function printproduit(){
        $link="produit";
        $antene = $this->getUser()->getAntene();
        $produit = $this->em->getRepository(Produit::class)->findAll();
        //dd($antene);
        $template = $this->renderView('admin/print/printproduit.pdf.twig', [
            "produit" => $produit,
            "antene" => $antene,
        ]);
        
       // dd($this->result);
       return $this->returnPDFResponseFromHTML($template, "Liste des Chambres"); 
    }

    
    /**
     * @Route("/deleteproduit", name="delete-produit" , methods={"DELETE"})
     */
    public function deleteproduit(Request $request){
        $link="listproduit";
        $id = intval($request->get('id'));
        try{
            if($id){
                $produit = $this->em->getRepository(Produit::class)->find($id);
                if($produit){
                    if(count($produit->getEntreitems()) === 0){
                        $this->em->remove($produit);
                        $this->em->flush();
                        $this->setlog("SUPPRIMER","Le Produit ".$this->getUser()->getUsername().
                        " a Supprimer le Produit ".$produit->getNom(),"PRODUIT",$produit->getId());
                        $this->successResponse("Produit supprimé ", $link);
                    }else{
                        $this->log("Impossible de supprimer ce produit, certains informations y sont liées.", $link);
                    }
                }else{
                    $this->log("Produit introuvable.", $link);
                }
            }else{
                $this->log("Veuillez choisir un produit.", $link);
            }
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return new JsonResponse($this->result);
   }

}
