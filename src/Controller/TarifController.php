<?php

namespace App\Controller;

use App\Entity\Entene;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Tarif;
use App\Entity\Module;
use App\Entity\Typechambre;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class TarifController extends DefaultController
{
   

    /**
     * @Route("/indextarif", name="index-tarif", methods={"GET"})
     */
    public function indextarif()
    {
        $link="indextarif";
        try {            
            if($this->getUser()->getIsAdmin()){
                $types = $this->em->getRepository(Typechambre::class)->findAll();
                $data = $this->renderView('admin/tarif/indexadmin.html.twig', [
                    "antennes" => $this->getAllAntennes(),
                    "types" => $types
                ]);
            }else{
                $antenne = $this->getUser()->getAntene();
                $types = $this->em->getRepository(Typechambre::class)->findBy(['antene'=>$antenne]);
                $data = $this->renderView('admin/tarif/index.html.twig', [
                    "antenne" => $antenne,
                    'types' => $types
                ]);
            }
            $this->successResponse("Liste des tarif ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }


    /**
     * @Route("/tarifadd", name="tarif-add", methods={"POST"})
     */
    public function tarifAdd(Request $request)
    {
        $link = "indextarif";
        try {
            $nom =  $request->get('nom');
            // $dure =  $request->get('dure');
            $type =  $request->get('type');
            $typetarif =  $request->get('typetarif');            
            $antenneID = intval($request->get('antenne'));
            $prix =  floatval($request->get('price'));
            $description =  $request->get('description');

            if($antenneID){
                $antenne = $this->em->getRepository(Entene::class)->find($antenneID);
            }else{
                $antenne = $this->getUser()->getAntene();
            }
            if($antenne){
                if($nom){
                        if($typetarif){                            
                            $typech = $this->em->getRepository(Typechambre::class)->find($type);
                            if($typech){
                                if($prix != 0){
                                    $existTarif = $this->em->getRepository(Tarif::class)->findOneBy(['typechambre'=>$typech, 'type'=>$typetarif, 'antenne'=>$antenne]);
                                    if(is_null($existTarif)){
                                        $tarif=new Tarif();
                                        $tarif->setNom($nom);
                                        $tarif->setType($typetarif);
                                        // $tarif->setDuree($dure);
                                        $tarif->setTypechambre($typech);
                                        $tarif->setPrix($prix);
                                        $tarif->setDescription($description);
                                        $tarif->setAntenne($antenne);
                            
                                        $this->em->persist($tarif);
                                        $this->em->flush(); 
                                        $this->setlog("AJOUTER","L'utilisateur ".$this->getUser()->getUsername().
                                        " a ajouté un tarif  ".$tarif-> getNom(),"TARIF",$tarif->getId());           
                                        $this->successResponse("Tarif ajouté ",$link);  
                                    }else{
                                        $this->log("Un tarif pour la  ".$typetarif." dans la chambre de type ".$typech->getNom()." a déja été enrégistré pour l'antenne ".$antenne->getNom().". Montant : ".$existTarif->getPrix(), $link);
                                    }
                                }else{
                                    $this->log("Le prix  est obligatoire.", $link);
                                }
                            }else{
                                $this->log("Sélectionner un type de chambre.", $link);
                            }
                        }else{
                            $this->log("Le type de tarif (NUITEE ou SIESTE) est obligatoire.", $link);
                        }
                    
                }else{
                    $this->log("Le nom est obligatoire", $link);
                }
            }else{
                $this->log("Aucune antenne sélectionnée", $link);
            }
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), "$link");
        }
        return new JsonResponse($this->result);
        
    }

    /**
     * @Route("/deletetarif", name="tarif-delete", methods={"DELETE"})
     */
    public function delete(Request $request)
    {
        $link="indextarif";
        $id = intval($request->get("id"));
        try {
            $tarif = $this->em ->getRepository(Tarif::class)->find($id);
            if($tarif){                
                $this->em->remove($tarif);
                $this->em->flush();
                $this->setlog("SUPPRIMER","L'utilisateur ".$this->getUser()->getUsername().
                " a supprimé un tarif  ".$tarif-> getNom(),"TARIF",$tarif->getId());           
                $this->successResponse("Tarif Supprimé ", $link); 
            }else{
                $this->log("Tarif innexistant ",$link);
            }            
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }


    /**
     * @Route("/edit/{id}/edit", name="tarif-edit")
     */
    public function edit($id)
    {
        $link="indextarif";

        try {           
            $tarif = $this->getDoctrine()->getRepository(Tarif::class)->find(intval($id));
            if (!$tarif) {
                $this->log("Tarif inexistant", $link);
            }else{
                $data = $this->renderView('admin/tarif/edit.html.twig', [
                    'tarif' => $tarif              
                ]);
                $this->successResponse("Tarif modifié  ", $link, $data);
            }            
                        
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }
///
    /**
     * @Route("/edittarif/edit", name="tarif-Redit", methods={"POST"})
     */
    public function modifierTarif(Request $request): Response
    {
        $link = "indextarif";
        $id = intval($request->get("id"));
        
        try {
            $nom =  $request->get('nom');
            // $dure =  $request->get('dure');
            $type =  $request->get('type');
            $typetarif =  $request->get('typetarif');   
            $prix =  floatval($request->get('price'));
            $description =  $request->get('description');
            if($nom){
                    if($typetarif){                            
                        $typech = $this->em->getRepository(Typechambre::class)->find($type);
                        if($typech){
                            if($prix != 0){
                                $tarif =  $this->em->getRepository(Tarif::class)->find($id);
                                if(!$tarif){
                                    $this->log("Tarif inexistant", $link);
                                }else{
                                    $existTarif = $this->em->getRepository(Tarif::class)->findOneBy(['typechambre'=>$typech, 'type'=>$typetarif, 'antenne'=>$tarif->getAntenne()]);
                                    if($existTarif && $existTarif->getId() != $tarif->getId()){
                                        $this->log("Un tarif pour la  ".$typetarif." dans la chambre de type ".$typech->getNom()." a déja été enrégistré pour l'antenne ".$tarif->getAntenne()->getNom().". Montant : ".$existTarif->getPrix(), $link);
                                    }else{
                                        $tarif->setNom($nom);
                                        $tarif->setType($typetarif);
                                        $tarif->setTypechambre($typech);
                                        $tarif->setPrix($prix);
                                        $tarif->setDescription($description);                            
                                        $this->em->persist($tarif);
                                        $this->em->flush(); 
                                        $this->setlog("MODIFIER","L'utilisateur ".$this->getUser()->getUsername().
                                        " a modifié un tarif  ".$tarif-> getNom(),"TARIF",$tarif->getId());           
                                        $this->successResponse("Tarif modifié ",$link);  
                                    }
                                }
                            }else{
                                $this->log("Le prix  est obligatoire.", $link);
                            }
                        }else{
                            $this->log("Sélectionner un type de chambre.", $link);
                        }
                    }else{
                        $this->log("Le type de tarif (NUITEE ou SIESTE) est obligatoire.", $link);
                    }
            }else{
                $this->log("Le nom est obligatoire.", $link);
            }
           
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
        
    }


    /**
     * @Route("/printtypechambre", name="print-typechambre")
     */
    public function printtypechambre(){
            $link="indextarif";
            $user = $this->getUser();
            if($user->getIsAdmin()){
                $tarifs = $this->em->getRepository(Tarifs::class)->findAll();
            }else{
                $tarifs = $user->getAntene()->getTarifs();
            }
            $types =$this->em->getRepository(Typechambre::class)->findAll();
            $template = $this->renderView('admin/print/printtypechambre.pdf.twig', [
                "tarifs" => $tarifs,
                "types" => $types,
                "antene" => $user->getAntene()
            ]);
        
       // dd($this->result);
       return $this->returnPDFResponseFromHTML($template, "Liste des types de chambres"); 
    }

    /**
     * @Route("/printtarif", name="print-tarif")
     */
    public function printtarif(){
        $link="index-tarrif";
            $antene = $this->getUser()->getAntene();
            $types =$this->em->getRepository(Typechambre::class)->findAll();
            //dd($antene);
            $tarifs =$this->em->getRepository(Tarif::class)->findAll();
            $template = $this->renderView('admin/print/printtarif.pdf.twig', [
                "tarifs" => $tarifs,
                "types" => $types,
                "antene" => $antene
            ]);
        
       // dd($this->result);
       return $this->returnPDFResponseFromHTML($template, "Liste des tarifs"); 
    }


}
