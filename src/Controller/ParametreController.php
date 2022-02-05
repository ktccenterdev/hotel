<?php

namespace App\Controller;

use App\Entity\Compte;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EnteneRepository;
use App\Entity\Parametre;
use Symfony\Component\HttpFoundation\JsonResponse;

class ParametreController extends DefaultController
{
    /**
     * @Route("/paramvue", name="param-vue", methods={"GET"})
     */
    public function paramvue()
    {
        $link="paramvue";              
        
        try {
            $parametre = current($this->em->getRepository(Parametre::class)->findAll());
            $compteCredits = $this->em->getRepository(Compte::class)->findBy(['type'=>'CREDIT']);
            $compteDebits = $this->em->getRepository(Compte::class)->findBy(['type'=>'DEBIT']);
            $data = $this->renderView('admin/parametre/index.html.twig', [
                "param"=>$parametre,
                "compteCredits"=>$compteCredits,
                "compteDebits"=>$compteDebits
                ]);
            $this->successResponse("Liste des parametres affichée", $link, $data);
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }

 /**
     * @Route("/parameteredit", name="parameter-edit", methods={"POST"})
     */
    public function parametreEdit(Request $request){
        $link = "paramvue";
		
        try {
            $id = $request->get("id");
            $nom = $request->get("nom");
            $description = $request->get("description");
            $site = $request->get("site");
            $email = $request->get("email");
            $bp = $request->get("bp");
            $compteHebergement = $request->get("compteHebergement");
            $compteFournisseur = $request->get("compteFournisseur");
            $pourcentagereservation = $request->get("pourcentagereservation");
            $compteH = $this->em->getRepository(Compte::class)->find($compteHebergement);
            $compteF = $this->em->getRepository(Compte::class)->find($compteFournisseur);
			
			$logo = $request->files->get("file1");
            //dd($logo);
			
            if(is_null($compteH)){
                $this->log("Le compte d'hébergment ne peut être null, veuillez choisir un compte.", $link);
            }elseif(is_null($compteF)){
                $this->log("Le compte forunisseur ne peut être null, veuillez choisir un compte.", $link);
			
            }elseif($compteH === $compteF){
                $this->log("Les comptes d'hébergement et fournisseur doivent être différents.", $link);
            }else{
                $parametre = $this->em->getRepository(Parametre::class)->find($id);
                if(is_null($parametre)){
                    $this->log("Paramètres introuvables.", $link);
                }else{
                    $parametre->setNom($nom);
                    $parametre->setDescription($description);
                    $parametre->setSite($site);
                    $parametre->setEmail($email);
                    $parametre->setBp($bp);
                    $parametre->setCompteHebergement($compteH);
                    $parametre->setCompteFournisseur($compteF);
                    $parametre->setPourcentagereservation($pourcentagereservation);
					if(!empty($logo) ){
						$path1 = md5(uniqid()).'.'.$logo->guessExtension();
						$logo->move($this->getParameter('Logo'), $path1);
						$parametre->setLogo($path1);
					}
                    $this->em->persist($parametre);
                    $this->em->flush();
                    $this->successResponse("Parametres modifiés ",$link); 
                }
                
            }
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return $this->json($this->result);
    }
}
