<?php

namespace App\Controller;

use App\Entity\Beneficiaire;
use App\Entity\Compte;
use App\Entity\Entene;
use App\Entity\Entrestock;
use App\Entity\Fournisseur;
use App\Entity\SortieFinanciere;
use App\Entity\User;
use App\Entity\Beneficiare;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SortieFinanciereController extends DefaultController
{

    /**
     * @Route("/sortiefinanciere", name="sortie-financiere-index", methods={"GET"})
     */
    public function index(Request $request)
    {
        $link="sortiefinanciere";  
        $user = $this->getUser();
        $sortieFournisseurs = [];
        $sortieAutres = [];
        $totalAutres = [];
        $totalFournisseurs = [];
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $today = $request->get('today');
        try {
            if($user){
                if($user->getIsadmin()){
                    $antennes = $this->getAllAntennes();
                    foreach ($antennes as $antenne) {
                        $montantFournisseurs = 0;
                        $montantAutres = 0;
                        $listeFournisseurs = $this->em->getRepository(SortieFinanciere::class)->findByTypeBeneficiaire("fournisseur", $antenne->getId(), $debut, $fin, $today);
                        $listeAutres = $this->em->getRepository(SortieFinanciere::class)->findByTypeBeneficiaire("autre", $antenne->getId(), $debut, $fin, $today);
                        $sortieFournisseurs[$antenne->getId()] = $listeFournisseurs;
                        $sortieAutres[$antenne->getId()] = $listeAutres;
                        $montantFournisseurs += $this->sommeMontant($listeFournisseurs);
                        $montantAutres  += $this->sommeMontant($listeAutres);
                        $totalFournisseurs[$antenne->getId()] = $montantFournisseurs;
                        $totalAutres[$antenne->getId()] = $montantAutres;
                    }         
                  //  dd($totalAutres);           
                    $data = $this->renderView('admin/sortie_financiere/indexadmin.html.twig', [
                        "sortieFournisseurs" => $sortieFournisseurs,
                        "totalFournisseurs" => $totalFournisseurs,
                        "sortieAutres" => $sortieAutres,
                        "totalAutres" => $totalAutres,
                        "antennes" => $antennes,
                        "debut" => $debut,
                        "fin" => $fin,
                        "today" => $today                   
                     ]);
                    
                }else{
                    $antenne = $user->getAntene();
                    $sortieFournisseurs = $this->em->getRepository(SortieFinanciere::class)->findByTypeBeneficiaire("fournisseur", $antenne->getId(), $debut, $fin, $today);
                    $sortieAutres = $this->em->getRepository(SortieFinanciere::class)->findByTypeBeneficiaire("autre", $antenne->getId(), $debut, $fin, $today);
                    $data = $this->renderView('admin/sortie_financiere/index.html.twig', [
                        "sortieFournisseurs" => $sortieFournisseurs,
                        "sortieAutres" => $sortieAutres,
                        "totalFournisseurs" => $this->sommeMontant($sortieFournisseurs),
                        "totalAutres" => $this->sommeMontant($sortieAutres)
                    ]);
                }
                $this->successResponse("Sorties financières affichées ", $link, $data);
            }else{
                return $this->redirectToRoute('login');
            }
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
     
        return new JsonResponse($this->result);
    }

        /**
     * @Route("/sortiefinancieretype", name="sortie-financiere-type", methods={"GET"})
     */
    public function sortieType(Request $request)
    {
        $type = $request->get('type');        
        $link=$this->generateUrl("sortie-financiere-type",['type'=>$type]);
        try {
            if($type){                
                $sorties = $this->em->getRepository(SortieFinanciere::class)->findByTypeBeneficiaire($type);
              //  dd($sorties);
                $data = $this->renderView('admin/sortie_financiere/type.html.twig', [
                    "sorties" => $sorties,
                    "type" => $type
                ]);                
                $this->successResponse("Sorties financières de type ".$type." affichées ", $link, $data);
            }else{
                $this->log("Aucun type sélectionné.", $link);
            }
            
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }

    /**
     * @Route("/newsortiefinancierefournisseur", name="new-sortie-financiere-forunisseur", methods={"GET"})
     */
    public function newSortieFournisseur(Request $request)
    {      
        $link="newsortiefinancierefournisseur";
        try {
          
            $factures = $this->em->getRepository(Entrestock::class)->findBy(['etat'=>0]);
           // dd($factures);
            $data = $this->renderView('admin/sortie_financiere/newsortiefournisseur.html.twig', [
                "factures" => $factures,
                "type"=>'fournisseur'
            ]);
            $this->successResponse("Factures fournisseurs impayées ", $link, $data);
            
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }

    /**
     * @Route("/newsortiefinanciereautre", name="new-sortie-financiere-autre", methods={"GET"})
     */
    public function newSortieAutre(Request $request)
    {      
        $link="newsortiefinanciereautre";
        try {
            $comptes = $this->em->getRepository(Compte::class)->findBy(['type'=>'DEBIT']);
            $beneficiaires = $this->em->getRepository(User::class)->findAll();
            $data = $this->renderView('admin/sortie_financiere/newsortieautre.html.twig', [
                "comptes" => $comptes,
                "type"=>'autre',
                "beneficiaires" => $beneficiaires
            ]);
            $this->successResponse("Autre nouveau règlement ", $link, $data);
            
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }

        /**
     * @Route("/savesortiefournisseur", name="save-sortie-fournisseur", methods={"POST"})
     */
    public function saveSortieFournisseur(Request $request)
    {

        $link="sortiefinanciere";
        $factureID = $request->get('factureID');
        $montant = $request->get('montant');
        try {
            if($factureID){
                $facture = $this->em->getRepository(Entrestock::class)->find($factureID);                
                if($facture){
                    //add fournisseur to beneficiaire
                    $beneficiaire = $this->em->getRepository(Beneficiaire::class)->findOneBy(['fournisseur'=>$facture->getFournisseur()->getId()]);
                    if(is_null($beneficiaire)){
                        $beneficiaire = new Beneficiaire();
                        $beneficiaire->setFournisseur($facture->getFournisseur());
                        $beneficiaire->setType("fournisseur");
                        $this->em->persist($beneficiaire);
                        $this->em->flush();                        
                    }
                    $code = $this->genererCode("D-");
                    $sortie = new SortieFinanciere();
                    $sortie->setBeneficiaire($beneficiaire);
                    $sortie->setMotif("Facture de ".$facture->getFournisseur()->getNom()." du ".$facture->getDate()->format("d-m-Y H:i"));;
                    $sortie->setCommentaire($facture->getCommentaire());
                    $sortie->setMontant($montant);
                    $sortie->setOperateur($this->getUser());
                    $sortie->setAntenne($this->getUser()->getAntene());
                    $sortie->setCode($code);
                    $sortie->setCompte($this->parametre->getCompteFournisseur());
                    $this->em->persist($sortie);
                    $this->em->flush();
                    if($sortie->getId()){
                        $facture->setEtat(true);
                        $this->em->persist($facture);     
                        $this->em->flush();
                        $this->setlog("AJOUER","LA SortieFinanciere ".$this->getUser()->getUsername().
                        " a Ajouer le Produit ".$facture->getFournisseur()->getNom(),"SortieFinanciere",$facture->getId());  
                        $this->successResponse("Sortie ajoutée avec succès ", $link);                 
                    }else{
                        $this->log("Quelque chose à mal tourné, veuillez réessayer.", $link);
                    }

                }else{
                    $this->log("Facture inexistante. ", $link);
                }
            }else{
                $this->log("Aucune facture sélectionnée", $link);
            }
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }

    /**
     * @Route("/savesortieautre", name="save-sortie-autre", methods={"POST"})
     */
    public function saveSortieAutre(Request $request)
    {
        $link  = "sortiefinanciere";
        try {
            $motif = $request->get("motif");
            $operateur = $this->getUser();
            $commentaire = $request->get("commentaire");
            $beneficiaireID = $request->get("beneficiaireID");
            $montant = floatval($request->get("montant"));
            $idCompte = intval($request->get("compte"));
            $code = $this->genererCode("D-");
            if(!is_null($motif)){
                if(!is_null($montant)){
                    if (!is_null($idCompte)) {
                        $compte = $this->em->getRepository(Compte::class)->find($idCompte);
                        if(!is_null($compte)){ 
                            if(is_null($beneficiaireID)){
                                $this->log("Veuillez renseigner un bénéficiaire.", $link);
                            }else{
                                $user = $this->em->getRepository(User::class)->find($beneficiaireID);
                                $beneficiaire = $this->em->getRepository(Beneficiaire::class)->findOneBy(['autre'=>$user->getId()]);
                                if(is_null($beneficiaire)){
                                    $beneficiaire = new Beneficiaire();
                                    $beneficiaire->setAutre($user);
                                    $beneficiaire->setType("autre");
                                    $this->em->persist($beneficiaire);
                                    $this->em->flush();                        
                                }
                                $sortie = new SortieFinanciere();
                                $sortie->setCode($code);
                                $sortie->setMotif($motif);
                                $sortie->setCommentaire($commentaire);
                                $sortie->setCompte($compte);
                                $sortie->setMontant($montant);
                                $sortie->setOperateur($operateur);
                                $sortie->setBeneficiaire($beneficiaire);
                                $sortie->setAntenne(($operateur->getAntene()));
                               // dd($sortie);
                                $this->em->persist($sortie);
                                $this->em->flush();
                                $this->setlog("AJOUER","LA SortieFinanciere ".$this->getUser()->getUsername().
                                " a Ajouer le Produit ".$sortie->getOperateur()->getNom(),"SortieFinanciere",$sortie->getId()); 
                                $this->successResponse("Sortie ajoutée ", $link);
                            }                                
                        }else{
                            $this->log("Ce compte n'existe pas.", $link);
                        }
                       
                    } else {
                        $this->log("Le compte ne peut être null.", $link);
                    }                    
                }else{
                    $this->log("Le montant peut être null.", $link);
                }
            }else{
                $this->log("Le motif ne peut être null.", $link);
            }            
            return new JsonResponse($this->result);
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
            return new JsonResponse($this->result, 400);
        }
    }

    /**
     * @Route("/sortiefinanciereshow", name="sortie-financiere-show", methods={"GET"})
     */
    public function show(Request $request)
    {
        $id = intval($request->get("id"));
        $link = $this->generateUrl("sortie-financiere-show", ["id"=>$id]);        
        try {   
            $sortie = $this->em->getRepository(SortieFinanciere::class)->find($id); 
            if(!is_null($sortie)){
                $comptes = $this->em->getRepository(Compte::class)->findBy(["type"=>"DEBIT"]);
                $users = $this->em->getRepository(User::class)->findAll();
                $fournisseurs = $this->em->getRepository(Fournisseur::class)->findAll();
                $data = $this->renderView('admin/sortie_financiere/view.html.twig', [
                    "sortie" => $sortie,
                    "comptes" => $comptes,
                    "users" => $users,
                    "fournisseurs" => $fournisseurs
                ]);
                $this->successResponse("Détails sur la sortie affichés ", $link, $data);
                
            }else{
                $this->log("Sortie financière inexistante!", $link);
            }
            return new JsonResponse($this->result);
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
            return new JsonResponse($this->result, 400);
        }
    }

    /**
     * @Route("/sortiefinanciereupdate", name="sortie-financiere-update", methods={"PUT"})
     */
    public function update(Request $request)
    {
        $id = intval($request->get('id'));    
        $link = $this->generateUrl("sortie-financiere-show", ['id'=>$id]);

        try {
            $motif = $request->get("motif");
            $commentaire = $request->get("commentaire");
            $beneficiaireID = intval($request->get("beneficiaireID"));
            $montant = floatval($request->get("montant"));
            $idCompte = intval($request->get("compte"));
            //vérification des champs
            $sortie = $this->em->getRepository(SortieFinanciere::class)->find($id);
            if(!is_null($sortie)){
                if(!is_null($montant)){
                    if(!is_null($motif)){
                        if(!is_null($idCompte)){
                            $compte = $this->em->getRepository(Compte::class)->find($idCompte);
                            if(!is_null($compte)){                                
                                if(!is_null($beneficiaireID)){
                                    $beneficiaire = $sortie->getBeneficiaire();
                                    if ($beneficiaire->getType() === "autre") {
                                        $user = $this->em->getRepository(User::class)->find($beneficiaireID);
                                        if($user){
                                            if($beneficiaire->getAutre()->getId() !== $user->getId()){
                                                $beneficiaire = new Beneficiaire();
                                                $beneficiaire->setAutre($user);
                                                $beneficiaire->setType("autre");
                                                $this->em->persist($beneficiaire);
                                                $this->em->flush();
                                                $this->setlog("AJOUER","Le Beneficiaire ".$this->getUser()->getUsername().
                                                " a Ajouer le Beneficiaire ".$beneficiaire->getFournisseur(),"Beneficiaire",$beneficiaire->getId());
                                            }
                                        }else{
                                            $this->log("Le bénéficiaire introuvable.",$link);
                                        }
                                    }else{
                                        if($compte->getId() === $this->parametre->getCompte_fournisseur()->getId()){
                                            $fournisseur = $this->em->getRepository(Fournisseur::class)->find($beneficiaireID);
                                            if($beneficiaire->getAutre()->getId() !== $fournisseur->getId()){
                                                $beneficiaire = new Beneficiare();
                                                $beneficiaire->setAutre($fournisseur);
                                                $beneficiaire->setType("fournisseur");
                                                $this->em->persist($beneficiaire);
                                                $this->em->flush();
                                                $this->setlog("AJOUER","Le Beneficiaire ".$this->getUser()->getUsername().
                                                " a Ajouer le Beneficiaire ".$beneficiaire->getFournisseur(),"Beneficiaire",$beneficiaire->getId());
                                            }
                                        }else{
                                            $this->log("Le Compte fournisseur renseigné est différent de celui indiqué dans les paramètres.",$link);
                                        }                                        
                                    }
                                    $sortie->setBeneficiaire($beneficiaire);
                                    $sortie->setCompte($compte);
                                    $sortie->setMontant($montant);
                                    $sortie->setMotif($motif);
                                    $sortie->setCommentaire($commentaire);
                                    $this->em->persist($sortie);
                                    $this->em->flush();
                                    $this->setlog("AJOUER","Le Beneficiaire ".$this->getUser()->getUsername().
                                    " a Ajouer le Beneficiaire ".$beneficiaire->getFournisseur(),"Beneficiaire",$beneficiaire->getId());
                                    $this->successResponse("Sortie modifiée avec succès.", $link);
                                }else{
                                    $this->log("Le bénéficiaire ne peut être vide.",$link);
                                }     
                            }else{
                                $this->log("Compte non existant, veuillez choisir un autre!", $link);
                            }
                        }else{
                            $this->log("Le compte ne peut être vide.",$link);
                        }
                    }else{
                        $this->log("Le motif ne peut être vide.",$link);
                    }
                }else{
                    $this->log("Le montant ne peut être vide.",$link);
                }
            }else{
                $this->log("Veuillez sélectionner une sortie financière.",$link);
            }
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);            
        }
        return new JsonResponse($this->result);
    }

    /**
     * @Route("/sortiefinancieredelete", name="sortie-financiere-delete", methods={"DELETE"})
     */
    public function delete(Request $request)
    {
        $link="sortiefinanciere";
        $id = $request->get("id");
        try {
            $sortie = $this->em->getRepository(SortieFinanciere::class)->find($id);                
            if(!is_null($sortie)){
                    $this->em->remove($sortie);
                    $this->em->flush();
                    $this->successResponse("Sortie financière Supprimé ",$link);                   
            }else{
                $this->log("Sortie financiere inexistante, veuillez sélectionner une autre.", $link);
            }            
            return new JsonResponse($this->result);
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
            return new JsonResponse($this->result, 400);
        }
    }

    /**
     * @Route("/sortiefinancierprintone", name="sortie-financiere-print-one", methods={"GET"})
     */
    public function printOne(Request $request)
    {
        $id = $request->get("id");
        $link = $this->generateUrl("sortie-financiere-show", ['id'=>$id]);
        try {
            $sortie = $this->em->getRepository(SortieFinanciere::class)->find($id);   
            $antene = $this->getUser()->getAntene();             
            if(!is_null($sortie)){
                $template = $this->renderView('admin/print/printOneDepense.pdf.twig', [
                    "sortie" => $sortie,
                    "antene" => $antene
                ]);       
                return $this->returnPDFResponseFromHTML($template, "Sortie financière");         
            }else{
                $this->log("Sortie financiere inexistante, veuillez sélectionner une autre.", $link);
                return new JsonResponse($this->result);
            } 
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
            return new JsonResponse($this->result, 400);
        }
    }
}

