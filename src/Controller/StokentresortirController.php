<?php

namespace App\Controller;

use \DateInterval;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Tarif;
use App\Entity\Magasin;
use App\Entity\Produit;
use App\Entity\Chambre; 
use App\Entity\Entreitem;
use App\Entity\Allocation;
use App\Entity\Entrestock;
use App\Entity\Sortiritem;
use App\Entity\Demandeitem;
use App\Entity\Fournisseur;
use App\Entity\Sortirstock;
use App\Entity\Typechambre;
use App\Entity\Demandeaprovisoinment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StokentresortirController extends DefaultController
{

    /**
     * @Route("/indexentree", name="index-entree", methods={"GET"})
    */
    public function indexentree()
    {
        $link = "indexentree";
        if($this->getUser()){
            $user = $this->getUser();
            try {
                $fournisseurs = $this->em->getRepository(Fournisseur::class)->findAll();
                $produits = $this->em->getRepository(Produit::class)->findAll();

                if($user->getIsadmin()){
                    $magasins = $this->em->getRepository(Magasin::class)->findAll();
                }else{
                    $magasins = [$user->getAntene()->getMagasin()];
                }
                $data = $this->renderView('admin/gestionstock/entreestock.html.twig', [
                    "fournisseurs" => $fournisseurs,
                    "produits" => $produits,
                    "magasins" => $magasins
                ]);
                $this->successResponse("Ajouter des entres", $link, $data);
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirectToRoute('login');
        }
    }


    /**
     * @Route("/printrecuentrestock", name="print-recuentrestock", methods={"GET"})
     */
    public function printrecus(Request $request)
    {
        $link = "indexentree";
        if($this->getUser()){
            $user = $this->getUser();
            try {
                $link="print-recuentrestock";
                $entreestock =$this->em->getRepository(Entrestock::class)->find($request->get('id'));
                $tarifs =$this->em->getRepository(Tarif::class)->findAll();
                $template = $this->renderView('admin/print/recuentrestock.pdf.twig', [
                    "tarifs" => $tarifs,
                    "entreestock" => $entreestock,
                    "antene" => $user->getAntene()
                ]);
                return $this->returnPDFResponseFromHTML($template, "fiche des entrées de stock"); 
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirectToRoute('login');
        }
    }


    /**
     * @Route("/entrestockadd", name="entrestock-add", methods={"POST"})
     */
    public function entrestockadd(Request $request)
    {
        $link = "indexentree";
        if($this->getUser()){
            $user = $this->getUser();
            $antenne = $user->getAntene();
            try {

                $fourniseur =$request->get('fournisseur');
                $fourniseur = $this->em->getRepository(Fournisseur::class)->find($fourniseur);
                $commentaire =  $request->get('comment');
                $produits =  $request->get('produits');
                $magasin = $antenne->getMagasin();
             //   dd($produits);
                $entreestock = new Entrestock();
                $entreestock->SetUser($this->getUser());
                $entreestock->SetFournisseur($fourniseur);
                $entreestock->SetCommentaire($commentaire);
                $entreestock->SetDate(new \DateTime());
                $entreestock->SetMagasin($magasin); 
                $entreestock->SetEtat(0);                 
                $this->em->persist($entreestock);

                foreach ($produits  as $value) {
                    $item=new Entreitem();
                    $produit = $this->em->getRepository(Produit::class)->find($value["idproduit"]);
                    $item->setProduit($produit);
                    $item->SetEntre($entreestock);
                    $item->setQt($value["qt"]);
                    $item->setPu(floatval($value["pt"])/floatval($value["qt"]));
                    $item->setPixtotal(floatval($value["pt"]));
                    $item->setQt($value["qt"]);
                    $this->em->persist($item);
                    $this->em->flush();
                    $this->setlog("ENTREE","Le STOCK ".$this->getUser()->getUsername().
                    " a entré le stock ".$entreestock->getUser()->getNom(),"Entrestock",$entreestock->getId());                
                }
                $result = array("success"=>true,"entrestock"=>$entreestock);
                return new JsonResponse($result);
            } catch (\Exception $ex) {
                $result = array("success"=>false,"message"=>$ex->getMessage());
                return new JsonResponse($result);
            }
        }else{
            return $this->redirectToRoute('login');
        }


    }

    /**
     * @Route("/listeentrestockgenerale", name="listeentrestockgenerale", methods={"GET"})
     */
    public function listeentrestockgenerale(Request $request)
    {
        $link="listeentrestockgenerale";
        $user = $this->getUser();
        if($user){
            try {
                if($user->getIsadmin()){
                     
                    $magasing = $this->em->getRepository(Magasin::class)->findBy(['type' => 'Général']);
                    if($magasing){
                        $data = $this->renderView('admin/gestionstock/listeentrestock.html.twig', [
                        "entrestocks" => current($magasing)->GetEntrestocks()
                        ]);
                        $this->successResponse("Ajouter des entres", $link, $data);
                    }else{
                        $this->log("Aucun magasin enrégistré.", $link);
                    }
                }else {
                   
                    $magasing = $this->em->getRepository(Magasin::class)->findBy(['type' => 'Général']);
                    if($magasing){
                        $data = $this->renderView('admin/gestionstock/listeentrestock.html.twig', [
                        //"entrestocks" => current($magasing)->GetEntrestocks()->getAntene()
                       /* "entrestocks" => $magasing */
                       "entrestocks" => current($magasing)->GetEntrestocks()
                        ]);
                        $this->successResponse("Ajouter des entres", $link, $data);
                    }else{
                        $this->log("Aucun magasin enrégistré.", $link);
                    }
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
     * @Route("/detailentreestock/{id}", name="detailentreestock", methods={"GET"})
     */
    public function detailentreestock($id)
    {
        //$id =  $request->get('id');
        $link="detailentreestock";
        $user = $this->getUser();
        if($user){
            try {
                $entrestock = $this->em->getRepository(Entrestock::class)->find($id);
                //dd($entrestock->GetEntreitems());
                $data = $this->renderView('admin/gestionstock/detail.html.twig', [
                    "entrestocks" => $entrestock
                ]);
                
                $this->successResponse("Ajouter des entres", $link, $data); 
            } catch (\Exception $ex) {
                $result = array("success"=>false,"message"=>"probleme");
            }
            
            return $this->json($this->result);
        }else{
            return $this->redirectToRoute('login');
        }
       
    }

    /**
     * @Route("/detailsortiestock/{id}", name="detail-sortie-stock", methods={"GET"})
     */
    public function detailsortiestock($id)
    {
        $link="detail-sortie-stock";
        $user = $this->getUser();
        if($user){
            try {
                $sortiestocks = $this->em->getRepository(Sortirstock::class)->find($id);
                //dd($sortiestocks);
                $data = $this->renderView('admin/gestionstock/detailsortie.html.twig', [
                    "sortiestocks" => $sortiestocks
                ]);
                $this->successResponse("Détail sur la sortie du stock", $link, $data); 
            } catch (\Exception $ex) {
                $result = array("success"=>false,"message"=>"probleme");
            }
            return $this->json($this->result);
        }else{
            return $this->redirectToRoute('login');
        }
       
    }

    /**
     * @Route("/editsortiestock/{id}", name="edit-sortie-stock", methods={"GET"})
     */
    public function editsortiestock($id)
    {
        $link="edit-sortie-stock";
        $user = $this->getUser();
        if($user){
            try {
                $sortiestocks = $this->em->getRepository(Sortirstock::class)->find($id);
                $magasins=[];
                $employes=[];
                if($sortiestocks->getMagdest()){
                    $magasins = $this->em->getRepository(Magasin::class)->findAll();
                }else{
                    $employes = $this->em->getRepository(User::class)->findBy(["type" =>"EMPLOYE"]);
                }
                //dd($sortiestocks);
                $data = $this->renderView('admin/gestionstock/updatesortie.html.twig', [
                    "sortiestocks" => $sortiestocks,
                    "magasins" => $magasins,
                    "employes" => $employes
                ]);
                $this->successResponse("éditer la sortie du stock", $link, $data); 
            } catch (\Exception $ex) {
                $result = array("success"=>false,"message"=>"probleme");
            }
            return $this->json($this->result);
        }else{
            return $this->redirectToRoute('login');
        }
       
    }

    /**
     * @Route("/editionsortiestock", name="edition-sortie-stock", methods={"POST"})
     */
    public function editionsortiestock(Request $request)
    {
        $link="listsortis";
        $user = $this->getUser();
        
        if($user){
            try {
                $id =  intval($request->get('id'));

                $magdest =  intval($request->get('magdest'));
                $magasin = $this->em->getRepository(Magasin::class)->find($magdest);
                
                $responssable =  intval($request->get('responssable'));
                $employes = $this->em->getRepository(User::class)->find($responssable);

                $description =  $request->get('description');

                $sortirstock = $this->em->getRepository(Sortirstock::class)->find($id);
                
                if($magasin){
                    $sortirstock ->setMagdest($magasin);
                }
                if($employes){
                    $sortirstock->setResponsable($employes);
                }
                if($description){
                    $sortirstock ->setCommantaire($description);
                }
                //dd($sortirstock);
                $this->em->persist($sortirstock);
                $this->em->flush();
                $this->successResponse("Modification Sortie en stock Effectuée ",$link); 
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(),$link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirectToRoute('login');
        }
       
    }


    /**
     * @Route("/listsortis", name="index-sortis", methods={"GET"})
     */
    public function listsortis()
    {
        $link="sortis";
        $userg = $this->getUser();
        if($userg){
            try {
                $sortis = null;
                $magasins = $this->em->getRepository(Magasin::class)->findAll();
                $users = $this->em->getRepository(User::class)->findBy(["type" =>"EMPLOYE"]);
                $produits = $this->em->getRepository(Produit::class)->findAll();
                $sortirs = $this->em->getRepository(Sortiritem::class)->findAll();
                //dd($sortirs);
                $sortirstocks = $this->em->getRepository(Sortirstock::class)->findAll();
                $data = $this->renderView('admin/gestionstock/sortis.html.twig', [
                    "sortis" => $sortis,
                    "produits" => $produits,
                    "magasins" => $magasins,
                    "users" => $users,
                    "sortirs" => $sortirs,
                    "sortirstocks" => $sortirstocks
                ]);
                $this->successResponse("Liste des sortis ", $link, $data);

            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            // dd($this->result);
            return $this->json($this->result);
        }else{
            return $this->redirectToRoute('login');
        }
    }
   

    /**
     * @Route("/indexsortis", name="index-Rsortis", methods={"GET"})
     */
    public function indexlistsortis()
    {
        $link="sortis";
        $userg = $this->getUser();
        if($userg){
            try {
                $magasins = $this->em->getRepository(Magasin::class)->findAll();
                $users = $this->em->getRepository(User::class)->findBy([
                    "type" =>"EMPLOYE",
                    "antene" => $userg->getAntene()
                ]);
                $produits = $this->em->getRepository(Produit::class)->findAll();
                $sortirs = $this->em->getRepository(Sortiritem::class)->findAll();
                $sortirstocks = $this->em->getRepository(Sortirstock::class)->findAll();
                //dd($produits);
                $sortis = null;
                $data = $this->renderView('admin/gestionstock/index.html.twig', [
                    "sortis" => $sortis,
                    "produits" => $produits,
                    "magasins" => $magasins,
                    "users" => $users,
                    "sortirs" => $sortirs,
                    "sortirstocks" => $sortirstocks
                ]);
                $this->successResponse("Liste des sortis ", $link, $data);

            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirectToRoute('login');
        }
    }


    /**
     * @Route("/addsortitstock", name="add-sortitstock", methods={"POST"})
     */
    public function addsortitstock(Request $request)
    {
        $link="listsortis";
        $userg = $this->getUser();
        if($userg){
            try {

            
                $responsable_id =  $request->get('responsable_id');
                $responsable_id = $this->em->getRepository(User::class)->find($responsable_id);
                $magdest_id =  $request->get('magdest_id');
                $magdest_id = $this->em->getRepository(Magasin::class)->find($magdest_id);
    
                $commantaire =  $request->get('commantaire');
                $type =  $request->get('type');

            

                $sortirstock = new Sortirstock();
                $sortirstock->SetUser($this->getUser());
                $sortirstock->setResponsable($responsable_id);
                $sortirstock ->setCommantaire($commantaire);
                $sortirstock->SetDate(new \DateTime());
                $sortirstock ->setType($type);
                $sortirstock ->setMagdepart($this->getUser()->getAntene()->getMagasin());
                $sortirstock ->setMagdest($magdest_id);
                $this->em->persist($sortirstock);
                $this->em->flush();
                /* $this->setlog("SORTIT","Le stock ".$this->getUser()->getUsername().
                " a sortit le stock ".$sortirstock->getUser(),"Sortirstock",$sortirstock->getId()); */

                $produit =  $request->get('produit');
                array_shift($produit);

                $quantite =  $request->get('quantite');
                array_shift($quantite);
                $items = array_combine($produit,$quantite);

                foreach ($items as $produit => $quantite) {
                    $item = new Sortiritem();
                    //$produit = $this->em->getRepository(Produit::class)->find($value["idproduit"]);
                    $produit = $this->em->getRepository(Produit::class)->find($produit);
                    $item->setProduit($produit);
                    $item->setSortistock($sortirstock);
                    $item->setQt($quantite);
                    $this->em->persist($item);
                    $this->em->flush();
            
                }

                $this->successResponse("Sortit magasin Effectue ",$link);  
            }catch (\Exception $ex) {
                $this->log($ex->getMessage(), "index-Rsortis");
            } 
            return new JsonResponse($this->result);
        }else{
            return $this->redirectToRoute('login');
        }
    }


    
    public function getquantiteenstock($typeentene,$idmagasin,$produit,$magdepart=null,$magdest=null)
    {
            //RECUPERE LE TOTAL DES ENTRES 
            $typemag = $typeentene;
            $totalproduitentre=0;
            if($typemag=="GENERAL"){
                $totalentre= $this->em->getRepository(Entrestock::class)->findBy(["magasin" =>$idmagasin]);
                
                foreach ($totalentre as &$value) {
                    $sortirproduit= $this->em->getRepository(Entreitem::class)->findBy(["produit" =>$produit]);
                    
                    foreach ($sortirproduit as &$prod) {
                        $totalproduitentre=$totalproduitentre+$prod->getQt();
                        //dd($prod->getQt());
                    }
                }
            }else{
                
                $totalentre= $this->em->getRepository(Sortirstock::class)->findBy(["magdepart" =>$magdepart,"magdest"=>$magdest,"type"=>"MAGASIN"]);
                
                foreach ($totalentre as &$value) {
                    
                    $sortirproduit= $this->em->getRepository(Sortiritem::class)->findBy(["produit" =>$produit]);
                    
                    foreach ($sortirproduit as &$prod) {
                        $totalproduitentre=$totalproduitentre+$prod->getQt();
                        
                    }
                }
            }
           // dd($totalproduitentre);
            return $totalproduitentre;
    }

   
    public function checksortie($magdepart,$produit)
    {
        
        $totalproduitentre=0;
        
            $totalentre= $this->em->getRepository(Sortirstock::class)->findBy(["magdepart" =>$magdepart]);  
            // dd($totalentre);
            if(!empty($totalentre)){
                foreach ($totalentre as &$value) {
                    $sortirproduit= $this->em->getRepository(Sortiritem::class)->findBy(["produit" =>$produit]);
                    //dd($sortirproduit);
                    if(!empty($sortirproduit)){
                        foreach ($sortirproduit as &$prod) {
                            $totalproduitentre=$totalproduitentre+$prod->getQt();
                            
                        }
                    }
                    
                }
            }
            return $totalproduitentre;
    }

    
    /**
     * @Route("/rupturedestock", name="rupturedestock", methods={"GET"})
     */
    public function etatslock(Request $request){
        $link="rupturedestock";
        try {
            $idmagasion =$request->get('idmag');
            $magasin=$this->em->getRepository(Magasin::class)->find($idmagasion);
            $magdest=null;
            $magdepart=null;
            $departforsortie=$request->get('departforsortie');
            if(!empty($request->get('magdepart'))){
                $magdepart =$request->get('magdepart');
            }
            if(!empty($request->get('magdest'))){
                $magdest =$request->get('magdest');
            }
            if($magdest==null && $magdepart==null){
                $type="GENERAL";
            }else{
                $type="NOGENERAL";
            }
            
            $produits = $this->em->getRepository(Produit::class)->findAll();
            $stocks = array();
            foreach ($produits as &$value) {
            
                //dd($this->checksortie($idmagasion,$value->getId()));
                $checkinstock=$this->getquantiteenstock($type,$idmagasion,$value->getId(),$magdepart,$magdest)-
                $this->checksortie($departforsortie,$value->getId());
                if($checkinstock<=$value->getQtseuil()){

                    array_push($stocks, array("id"=>$value->getId(), 
                    "nomproduit"=>$value->getNom(), 
                    "photo"=>$value->getPhoto(),
                    "qtseuil"=>$value->getQtseuil(),
                    "qtstock"=>$checkinstock,
                    "repture"=>true
                    ));
                    

                }else{
                    array_push($stocks, array("id"=>$value->getId(), 
                    "nomproduit"=>$value->getNom(), 
                    "photo"=>$value->getPhoto(),
                    "qtseuil"=>$value->getQtseuil(),
                    "qtstock"=>$checkinstock,
                    "repture"=>false
                    ));
                }
                
            }
           /// dd($stocks);
            $data = $this->renderView('admin/gestionstock/etatstock.html.twig', [
                "stocks" => $stocks,
                "magasin"=>$magasin
            ]);
            $this->successResponse("etat du stock", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        
        return $this->json($this->result);
    }

    /**
     * @Route("/indexapprovisionement", name="index-approvisionement", methods={"GET"})
    */
    public function approvisionement()
    {
        $link="approvisionement";
        try {
            //$fournisseurs = $this->em->getRepository(Fournisseur::class)->findAll();
            //$margins = $this->em->getRepository(Magasin::class)->findAll();
            $produits = $this->em->getRepository(Produit::class)->findAll();
            $data = $this->renderView('admin/gestionstock/approvisionnement.html.twig', [
                "produits" => $produits,
            ]);
            $this->successResponse("Ajouter des entres", $link, $data);
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
        
    }

    /**
     * @Route("/addapprovisionement", name="add-approvisionement", methods={"POST"})
    */
    public function addapprovisionement(Request $request)
    {
        $link="addapprovisionement";
        try {
            $user = $this->getUser();
            $antene = $user->getAntene();
            $comments =  $request->get('description');
            $magasins = $this->em->getRepository(Magasin::class)->findBy([
                "antene" => $antene
            ]);
            
            $approvisionement = new Demandeaprovisoinment();
            $approvisionement->setCommentaire($comments);
            $approvisionement->setEtat(0);
            $approvisionement->setMagasin($magasins[0]);
            $approvisionement->setResponsabledemande($user);
            $approvisionement->setCreateat(new \DateTime('now'));
            $approvisionement->setDatedemande(new \DateTime('now'));
            $this->em->persist($approvisionement);
            //$this->em->flush();
            $this->setlog("AJOUTER","une Demandeaprovisoinment ".$this->getUser()->getUsername().
            " a fait une Demandeaprovisoinment ","Demandeaprovisoinment",$approvisionement->getId());


            $quantites =  $request->get('quantite');
            array_shift($quantites);
            
            $produits =  $request->get('produit');
            array_shift($produits);
            
            $items = array_combine($produits,$quantites);
            //dd($items);

            foreach($items as $produit => $quantite) {
                $item = new Demandeitem();
                $item->setDemande($approvisionement);
                $produit = $this->em->getRepository(Produit::class)->find($produit);
                $item->setProduit($produit);
                $item->setQuantite($quantite);
                $item->setCreateat(new \DateTime('now'));
                $this->em->persist($item);
                //$this->em->flush();

                //dd($produit,$$quantite);
            }
			$this->em->flush();
            $demande = $this->em->getRepository(Demandeaprovisoinment::class)->findAll();
            //dd($demande);
            //dd($demande[0]->getDemandeitems());
            $data = $this->renderView('admin/gestionstock/listapprovisionement.html.twig', [
                //"approvisionement" => $approvisionement,
                "demandes" => $demande
            ]);
            $this->successResponse("Liste des approvisionement ", $link, $data);
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
        
    }

    /**
     * @Route("/listapprovisionement", name="listapprovisionement", methods={"GET"})
     */
    public function listapprovisionement()
    {
        $link="approvisionement";

        try {
           
            if($this->getUser()->getIsadmin()){
                $demandes = $this->em->getRepository(Demandeaprovisoinment::class)->findAll();
            }else{
                //dd($this->getUser());
                $demandes = $this->em->getRepository(Demandeaprovisoinment::class)->findBy([
                    'responsabledemande' => $this->getUser()
                ]);
            }
            //dd($demande[0]->getDemandeitems());
            $data = $this->renderView('admin/gestionstock/listapprovisionement.html.twig', [
                //"approvisionement" => $approvisionement,
                "demandes" => $demandes
            ]);
            $this->successResponse("Liste des approvisionement ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }

    /**
     * @Route("/viewapprovisionement/{id}", name="viewapprovisionement", methods={"GET"})
     */
    public function viewapprovisionement($id)
    {
        $link="approvisionement";

        try {
            
            $demande = $this->em->getRepository(Demandeaprovisoinment::class)->find($id);
            //dd($demande);
            $data = $this->renderView('admin/gestionstock/viewapprovisionnement.html.twig', [
                //"approvisionement" => $approvisionement,
                "demande" => $demande
            ]);
            $this->successResponse("Liste des détails approvisionements ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }
    
    /**
     * @Route("/mesapprovisionement", name="mesapprovisionement", methods={"GET"})
     */
    public function mesapprovisionement()
    {
        $link="mesapprovisionement";

        try {
            
            $demandes = $this->em->getRepository(Demandeaprovisoinment::class)
                ->findBy([
                'responsabledemande' => $this->getUser()
            ]);
            //dd($demande);
            $data = $this->renderView('admin/gestionstock/listapprovisionement.html.twig', [
                "demandes" => $demandes
            ]);
            $this->successResponse("Liste des approvisionement ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }

    /**
     * @Route("/indextraiter/{id}", name="index-traiter", methods={"GET"})
     */
    public function indextraiter($id)
    {
        $link="traiter approvisionement";

        try {
            
            $demandes = $this->em->getRepository(Demandeaprovisoinment::class)->find($id);
            //dd($demande);
            $data = $this->renderView('admin/gestionstock/traitement.html.twig', [
                "demande" => $demandes
            ]);
            $this->successResponse("Liste des approvisionement ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }

    /**
     * @Route("/traiter", name="traiter", methods={"POST"})
     */
    public function traiter(Request $request)
    {
        $link="traiter approvisionement";

        try {
            $id =  $request->get('id');
            
            $comments =  $request->get('commantaire');
            //dd($comments);
            $demandes = $this->em->getRepository(Demandeaprovisoinment::class)->find($id);
            $demandes->setEtat(1);
            $demandes->setCommentaire($comments);
            $demandes->setUpdatedate(new \DateTime('now'));
            $this->em->persist($demandes);
            $this->em->flush();

            $produits =  $request->get('produit');
            $quantites =  $request->get('quantite');
            $items = array_combine($produits,$quantites);
            //dd($items);

            foreach($items as $produit => $quantite) {
                $item = $this->em->getRepository(Demandeitem::class)->findBy([
                    "demande" => $demandes,
                    "produit" => $produit,
                ]);
                $item[0]->setQuantite($quantite);
                $item[0]->setUpdateat(new \DateTime('now'));
                $this->em->persist($item[0]);
                $this->em->flush();

                //dd($produit,$$quantite);
            }
            
            //dd($demandes);
            
            $data = $this->renderView('admin/gestionstock/viewapprovisionnement.html.twig', [
                "demande" => $demandes
            ]);
            $this->successResponse("Liste des approvisionement ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }
	
	/**
     * @Route("/printsortistock", name="print-sorti-stock")
     */
    public function printsortistock(){
        $antene = $this->getUser()->getAntene();
        $sortirstocks = $this->em->getRepository(Sortiritem::class)->findAll();
        //dd($antene);
        $template = $this->renderView('admin/print/printsstock.pdf.twig', [
            "sortirstocks" => $sortirstocks,
            "antene" => $antene,
        ]);
        
       // dd($this->result);
       return $this->returnPDFResponseFromHTML($template, "Liste des sorties en stock"); 
    }
	
	/**
     * @Route("/printentrestock", name="print-entre-stock")
     */
    public function printentrestock(){
        $antene = $this->getUser()->getAntene();
        $magasing = $this->em->getRepository(Magasin::class)->findBy(['type' => 'Général']);
        //dd($antene);
        $template = $this->renderView('admin/print/printentstock.pdf.twig', [
            "entrestocks" => current($magasing)->GetEntrestocks(),
            "antene" => $antene,
        ]);
        
       // dd($this->result);
       return $this->returnPDFResponseFromHTML($template, "Liste des entrees en stock"); 
    }


    

    /**
     * @Route("/printappro", name="print-approvisionement")
     */
    public function printappro(){
      
        $antene = $this->getUser()->getAntene();
        $demandes = $this->em->getRepository(Demandeaprovisoinment::class)->findBy(['responsabledemande' => $this->getUser() ]);
        $template = $this->renderView('admin/print/printapprovisionement.pdf.twig', [
            "demandes" => $demandes,
            "antene" => $antene,
        ]);
        
       // dd($this->result);
       return $this->returnPDFResponseFromHTML($template, "Liste des Demandes d'approvisionement"); 
    }
    
}
