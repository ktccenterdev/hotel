<?php

namespace App\Controller;


use App\Entity\Fournisseur;
use App\Entity\Entene;
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



class FournisseurController extends DefaultController
{


    /**
     * @Route("/listfournisseur", name="index-fournisseur", methods={"GET"})
     */
    public function listfournisseur()
    {
        $link="listfournisseur";

        try {
            $fournisseurs = $this->em->getRepository(Fournisseur::class)->findAll();
            $data = $this->renderView('admin/fournisseur/index.html.twig', [
                "fournisseurs" => $fournisseurs,
            ]);
            $this->successResponse("Liste des fournisseurs affichée", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }

    /**
     * @Route("/addfournisseur", name="fournisseur-add", methods={"POST"})
     */
    public function addfournisseur(Request $request)
    {
        $link="listfournisseur";
        try {
            $nom =  $request->get('nom');
            $ville =  $request->get('ville');
            $localisation =  $request->get('localisation');
            $tel1 =  $request->get('tel1');
            $tel2 =  $request->get('tel2');
            $email =  $request->get('email');            
            if($nom){
                $fournisseur = $this->em->getRepository(Fournisseur::class)->findOneBy(['nom'=>$nom]);
                if($fournisseur){
                    $this->log("Un fournisseur portant ce nom existe déja.", $link);
                }else{
                    $four= new Fournisseur();
                    $four->setNom($nom);
                    $four->setVille($ville);
                    $four->setLocalisation($localisation);
                    $four->setTel1($tel1);
                    $four->setTel2($tel2);
                    $four->setEmail($email);
                    $this->em->persist($four);
                    $this->em->flush();
                    $this->setlog("AJOUTER","Le Fournisseur ".$this->getUser()->getUsername().
                    " a ajouter le Fournisseur ".$four->getNom(),"FOURNISSEUR",$four->getId());
                    $this->successResponse("Fournisseur ajouté !",$link); 
                }
                    
            }else{
                $this->log("Le nom est obligatoire.", $link);            
            }
            
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }

    /**
     * @Route("/printfournisseur", name="print-fournisseur")
     */
    public function printfournisseur(){
        $link="fournisseur";
        $antene = $this->getUser()->getAntene();
        $fournisseurs = $this->em->getRepository(Fournisseur::class)->findAll();
        $template = $this->renderView('admin/print/printfournisseur.pdf.twig', [
            "fournisseurs" => $fournisseurs,
            "antene" => $antene,
        ]);
       return $this->returnPDFResponseFromHTML($template, "Liste des fournisseurs", "L"); 
    }

        /**
         * @Route("/showfour/{id}", name="showfour")
         */
        public function showfour($id){
            $link="vuefour";
            try{
                $fournis=$this->em->getRepository(Fournisseur::class)->find($id);
                //dd($fournis);
                $data = $this->renderView('admin/fournisseur/view.html.twig', [
                    "fournis" => $fournis

                ]);
                $this->successResponse("vuefounisseur ", $link, $data);


            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            } 
            return $this->json($this->result);
        }   


        /**
         * @Route("/editfour/{id}/edit", name="editfour" ,methods={"GET"})
         */
        public function editfour(int $id){
            $link="editfour";
            try{
                $fournis=$this->em->getRepository(Fournisseur::class)->find($id);
                //dd($fournis);
                $data = $this->renderView('admin/fournisseur/edit.html.twig', [
                    "fournis" => $fournis

                ]);
                $this->successResponse("vue editFpournisseurs ", $link, $data);


            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            } 
            return $this->json($this->result);
        }   
            

        /**
         * @Route("/editfournisseur/edit", name="editfournisseur" , methods={"PUT"})
         */
        public function editfournisseur(Request $request): Response
        {
            $id = intval($request->get("id"));
            $link= $this->generateUrl("editfour", ['id'=>$id]);

            $nom = $request->get("nom");
            $ville = $request->get("ville");
            $localisation = $request->get("localisation");
            $tel1 = $request->get("tel1");
            $tel2 = $request->get("tel2");
            $email = $request->get("email");
            try{
                if($id){
                    if($nom){
                        $four = $this->em->getRepository(Fournisseur::class)->find($id);
                        if($four){
                            $four2 = $this->em->getRepository(Fournisseur::class)->findOneBy(['nom'=>$nom]);
                            if($four2 && $four2->getId() != $id){
                                $this->log("Un autre fournisseur possede deja ce nom.", $link);
                            }else{
                                $four->setNom($nom);
                                $four->setVille($ville);
                                $four->setLocalisation($localisation);
                                $four->setTel1($tel1);
                                $four->setTel2($tel2);
                                $four->setEmail($email);                           
                                $this->em->persist($four);
                                $this->em->flush($four);
                                $this->setlog("MODIFIER","Le Fournisseur ".$this->getUser()->getUsername().
                                 " a modifier le Fournisseur ".$four->getNom(),"FOURNISSEUR",$four->getId());  
                                $this->successResponse("Fournisseur Modifié !",$link);
                            }
                        }else{
                            $this->log("Fournisseur introuvable.", $link);
                        }
                    }else{
                        $this->log("Le nom est obligatoire", $link);
                    }
                }else{
                    $this->log("Aucun fournisseur sélectionné", $link);
                }
                     
                } catch (\Exception $ex) {
                    $this->log($ex->getMessage(), "index-fournisseur");
                }
                return new JsonResponse($this->result);
       
        }

    /**
     * @Route("/deletefour", name="delete-fournisseur" , methods={"DELETE"})
     */
    public function deletefour(Request $request){
        $link="listfournisseur";
        $id = intval($request->get('id'));
        try{
            if($id){
                $four = $this->em->getRepository(Fournisseur::class)->find($id);
                if($four){
                    if(count($four->getEntrestocks()) === 0){
                        $this->em->remove($four);
                        $this->em->flush();
                        $this->setlog("SUPPRIMER","Le Fournisseur ".$this->getUser()->getUsername().
                        " a supprimer le Fournisseur ".$four->getNom(),"FOURNISSEUR",$four->getId());
                        $this->successResponse("Fournisseur supprimé ", $link);
                    }else{
                        $this->log("Impossible de supprimer ce fournisseur, certains produits y sont liés.", $link);
                    }
                }else{
                    $this->log("Fournisseur introuvable.", $link);
                }
            }else{
                $this->log("Veuillez choisir un fournisseur.", $link);
            }
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return new JsonResponse($this->result);
   }



}
