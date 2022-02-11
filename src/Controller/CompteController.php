<?php

namespace App\Controller;

use App\Entity\Compte;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class CompteController extends DefaultController
{
    /**
     * @Route("/compte", name="compte-index", methods={"GET"})
     */
    public function index()
    {
        $link="compte-index"; 
        $userg = $this->getUser();
        if($userg){       
            try {
                $comptes = $this->em->getRepository(Compte::class)->findAll(); 
                $data = $this->renderView('admin/compte/index.html.twig', [
                    "listes" => $comptes
                ]);
                $this->successResponse("Liste des comptes affichée ", $link, $data);
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/comptecreate", name="compte-create", methods={"POST"})
     */
    public function create(Request $request)
    {
        $link="compte";
        $userg = $this->getUser();
        if($userg){
            try {
                $intitule = $request->get("intitule");
                $code = $request->get("code");
                $idParent = $request->get("parent") ? $request->get("parent") : null ;
                $niveau = 1;
                $type = $request->get("type");
                if(!is_null($intitule)){
                    if(!is_null($code)){
                        if (!is_null($type)) {
                            $checkIntitule = $this->em->getRepository(Compte::class)->findOneBy(['intitule'=>$intitule]);
                            if(is_null($checkIntitule)){
                                $checkCode = $this->em->getRepository(Compte::class)->findOneBy(['code'=>$code]);
                                if(is_null($checkCode)){
                                    if($idParent){
                                        $parent = $this->em->getRepository(Compte::class)->find($idParent);
                                        if(!is_null($parent)){
                                            $niveau = $parent->getNiveau() + 1;
                                        }
                                    }                       
                                    $compte = new Compte();
                                    $compte->setCode($code);
                                    $compte->setIntitule($intitule);
                                    $compte->setParent($idParent);
                                    $compte->setNiveau($niveau);
                                    $compte->setType($type);

                                    $this->em->persist($compte);
                                    $this->em->flush();
                                    $this->successResponse("Compte ajouté ", $link);
                                }else{
                                    $this->log("Ce code est déja enrégistré pour un autre compte.", $link);
                                }
                            }else{
                                $this->log("Cet intitulé existe déja.",$link);
                            }
                        } else {
                            $this->log("Le type de compte ne peut être null.", $link);
                        }
                        
                    }else{
                        $this->log("Le ne peut être null.", $link);
                    }
                }else{
                    $this->log("L'intitulé ne peut être null.", $link);
                }            
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirectToRoute('login');
        }
    }

     /**
     * @Route("/compteshow", name="compte-show", methods={"GET"})
     */
    public function show(Request $request)
    {
        $link="compte-index";
        $userg = $this->getUser();
        if($userg){
            $id = intval($request->get("id"));
            try {   
                $compte = $this->em->getRepository(Compte::class)->find($id); 
                if(!is_null($compte)){
                    $comptes = $this->em->getRepository(Compte::class)->findAll();
                    $data = $this->renderView('admin/compte/view.html.twig', [
                        "comptes" => $comptes,
                        "compte" => $compte
                    ]);
                    $this->successResponse("Détails sur le compte affichés ", $link, $data);
                }else{
                    $this->log("Compte inexistant!", $link);
                }
                return new JsonResponse($this->result);
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
                return new JsonResponse($this->result, 400);
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/compteupdate", name="compte-update", methods={"PUT"})
     */
    public function update(Request $request)
    {
        $userg = $this->getUser();
        if($userg){
            $id = intval($request->get('id'));       
            $link = $this->generateUrl("compte-show", ['id'=>$id]);
            try {
                $intitule = $request->get("intitule");
                $code = $request->get("code");
                $idParent = $request->get("parent") ? $request->get("parent") : null ;
                $niveau = $request->get("niveau");
                $type = $request->get("type");
                if(!is_null($intitule)){
                    if(!is_null($code)){
                        if (!is_null($type)) {
                            $compte = $this->em->getRepository(Compte::class)->find($id);
                            if(!is_null($compte)){
                                $checkIntitule = $this->em->getRepository(Compte::class)->findOneBy(['intitule'=>$intitule]);
                                if(!is_null($checkIntitule) && $compte->getId() !== $checkIntitule->getId()){
                                    $this->log("Cet intitulé existe déja.",$link);
                                }else{
                                    $checkCode = $this->em->getRepository(Compte::class)->findOneBy(['code'=>$code]);
                                    if(!is_null($checkCode) && $compte->getId() !== $checkCode->getId()){
                                        $this->log("Ce code existe déja utilisé.",$link);
                                    }else{
                                        if($idParent){
                                            $parent = $this->em->getRepository(Compte::class)->find($idParent);
                                            if(!is_null($parent) && $parent->getId() != $compte->getParent()){
                                                $niveau = $parent->getNiveau() + 1;
                                                $idParent = $parent->getId();
                                            }
                                        }else{
                                            $niveau = 1;
                                        }
                                        $compte->setCode($code);
                                        $compte->setIntitule($intitule);
                                        $compte->setParent($idParent);
                                        $compte->setNiveau($niveau);
                                        $compte->setType($type);
                                        $this->em->persist($compte);
                                        $this->em->flush();
                                        $this->successResponse("Compte modifié ", $link);                                    
                                    }     
                                }              
                            }else{
                                $this->log("Compte non existant, veuillez choisir un autre!", $link);
                            }
                        }else {
                            $this->log("Le type de compte ne peut être null.", $link);
                        }
                    }else {
                    $this->log("Le code ne peut être null.", $link);
                    }
                }else{
                    $this->log("L'intitulé ne peut être null.", $link);
                }            
                return new JsonResponse($this->result);
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
                return new JsonResponse($this->result, 400);
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/comptedelete", name="compte-delete", methods={"DELETE"})
     */
    public function delete(Request $request)
    {
        $link="compte";
        $userg = $this->getUser();
        if($userg){
            $id = $request->get("id");
            try {
                $compte = $this->em->getRepository(compte::class)->find($id);                
                if(!is_null($compte)){
                    if(count($compte->getAllocations()) === 0 && count($compte->getSortieFinancieres())===0){
                        $this->em->remove($compte);
                        $this->em->flush();
                        $this->successResponse("Compte Supprimé ",$link);
                    }else{
                        $this->log("Impossible de supprimer ce compte, des informations y sont liées.", $link);
                    }                 
                }else{
                    $this->log("Compte inexistant, veuillez sélectionner un autre.", $link);
                }            
                return new JsonResponse($this->result);
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
                return new JsonResponse($this->result, 400);
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }
}
