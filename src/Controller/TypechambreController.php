<?php

namespace App\Controller;

use App\Entity\Entene;
use App\Entity\Typechambre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TypechambreController extends DefaultController
{

    /**
     * @Route("/indextype", name="index-type", methods={"GET"})
     */
    public function indextype()
    {
        $link="indextype";
        
        try {
            
            if($this->getUser()->getIsadmin()){
                $antenes = $this->em->getRepository(Entene::class)->findAll();
                $data = $this->renderView('admin/typechambre/indexadmin.html.twig', [
                    "antenes" => $antenes
                ]);
            }else{
                $antene = $this->getUser()->getAntene();
                //$antenes[] = $this->em->getRepository(Entene::class)->find($id);

                $data = $this->renderView('admin/typechambre/index.html.twig', [
                    "antene" => $antene
                ]);
            }
            
            $this->successResponse("Liste des types de chambres affichés", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }

    /**
     * @Route("/createtype", name="createtype", methods={"POST"})
     */
    public function create(Request $request)
    {
        $link="indextype";

        try {
            $nom = $request->get("name");
            $antene = $request->get("antene");
            $description = $request->get("description");
            $photo = $request->files->get("photo");
            
            $types = $this->em->getRepository(Typechambre::class)->findOneBy(['nom'=>$nom]);
            if(is_null($types)){
                $types = new Typechambre();
                if($nom){
                    if($description){
                        $types->setNom($nom);
                        $types->setDescription($description);
                        if($antene){
                            $an = $this->em->getRepository(Entene::class)->find($antene);
                            
                        }else{
                            $an = $this->getUser()->getAntene();
                        }
                        $types->setAntene($an);
                        
                        if($photo){
                            $path = md5(uniqid()).'.'.$photo->guessExtension();
                            $photo->move($this->getParameter('typechambre'), $path);
                            $types->setPhoto($path);
                            //dd($types);
                            //$type->setRessourcename($photo->getClientOriginalName());
                        }

                        $this->em->persist($types);
                        $this->em->flush();
                        $this->setlog("AJOUTER","L'utilisateur ".$this->getUser()->getUsername().
                        " a ajouter un Type de chambre  ".$types-> getNom(),"TYPECHANMBRE",$types->getId()); 
                        $this->successResponse("Type de chambre ajouté ", $link);
                    }else{
                        $this->log("La description est obligatoire.", $link);
                    }
                }else{
                $this->log("Le nom est obligatoire.", $link);
                }
            }else{
                $this->log("Un type de chambre portant ce nom existe deja.", $link);
            }
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }


    /**
     * @Route("/editetype", name="type-chambre-show", methods={"GET"})
     */
    public function show(Request $request)
    {
        $link="indextype";
        $id = intval($request->get('id'));
        try {
            if($id){
                $chambre = $this->em->getRepository(Typechambre::class)->find($id);
                if(!is_null($chambre)){
                    $data = $this->renderView('admin/typechambre/show.html.twig', [
                        "chambre" => $chambre,
                    ]);
                }else{
                    $this->log("Type de chambre introuvable.", $link);
                }
            }else{
                $this->log("Aucun type sélectionnée.", $link);
            }
            $this->successResponse("Type de chambre affiché ", $link, $data);             
            
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }

        /**
     * @Route("/updatetypechambre", name="type-chambre-update", methods={"POST"})
     */
    public function update(Request $request)
    {        
        $id = intval($request->get('id'));
        $link= $this->generateUrl("type-chambre-show", ['id'=>$id]);
        $nom = $request->get("nom");
        $description = $request->get("description");
        try {
            $photo = $request->files->get("photo");
            if($id){
                $type = $this->em->getRepository(Typechambre::class)->find($id);
                if(!is_null($type)){
                   if($nom){
                        if($description){
                            $type->setNom($nom);
                            $type->setDescription($description);
                            if($photo){
                                $path = md5(uniqid()).'.'.$photo->guessExtension();
                                $photo->move($this->getParameter('typechambre'), $path);                
                                $type->setPhoto($path);
                            }
                            $this->em->persist($type);
                            $this->em->flush();
                            $this->setlog("MODIFIER ","le type de chambre ".$this->getUser()->getUsername().
                            " a modifier un type de chambre  ".$type-> getNom(),"Typechambre",$type->getId());      
                            $this->successResponse("Type de chambre modifié avec succès.", $link);
                        }else{
                            $this->log("La description est obligatoire.", $link);
                        }
                   }else{
                       $this->log("Le nom est obligatoire.", $link);
                   }
                }else{
                    $this->log("Type de chambre introuvable.", $link);
                }
            }else{
                $this->log("Aucun type sélectionnée.", $link);
            }
            $this->successResponse("Type de chambre affiché ", $link);             
            
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }


    /**
     * @Route("/deletetypechambre", name="type-chambre-delete", methods={"DELETE"})
     */
    public function delete(Request $request)
    {
        $link="indextype";
        $id = $request->get("id");
        try {
            if($id){
                $type = $this->em->getRepository(Typechambre::class)->find($id);
                if(!is_null($type)){
                    if(count($type->getChambres()) === 0){
                        $this->em->remove($type);
                        $this->em->flush();
                        $this->setlog("SUPPRIMER","le type de chambre ".$this->getUser()->getUsername().
                        " a supprimer un type de chambre  ".$type-> getNom(),"Typechambre",$type->getId()); 
                        $this->successResponse("Type de Chambre Supprimé ", $link); 
                    }else{
                        $this->log("Impossible de supprimer ce type, des chambres y sont liées.", $link);
                    }
                }else{
                    $this->log("Type de chambre introuvable.", $link);
                }
            }else{
                $this->log("Aucun type sélectionnée.", $link);
            }
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }


}
