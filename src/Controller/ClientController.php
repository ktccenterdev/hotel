<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Tarif;
use App\Entity\Entene;
use App\Entity\Chambre;
use App\Entity\Allocation;
use App\Entity\Reservation;
use App\Entity\Transaction;
use App\Entity\Typechambre;
use function PHPSTORM_META\type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ClientController extends DefaultController
{
      
     /**
     * @Route("/indexclient", name="index-client", methods={"GET"})
     */
    public function indexclient()
    {
        $link="indexclient";
        $antennes = $this->getAllAntennes();
        $user = $this->getUser();
        //dd($antennes);
        try {
          /*   foreach ($antennes as $antenne) {
                $antenne = $user->getAntene();
                //dd($antenne);
            } */
            
          $clients = $this->em->getRepository(User::class)->findBy(["type" => "CLIENT", "antene" =>$user->getAntene()]);
          //dd($clients); 
          
            $roles=$this->em->getRepository(Role::class)->findAll();
            $user = $this->getUser();
            if($user){
                if($this->getUser()->getIsadmin()){
                    $data = $this->renderView('admin/clients/indexadmin.html.twig', [
                         "roles"=>$roles,
                         "antennes"=>$this->getAllAntennes()
                     ]);
                }else{
                    $data = $this->renderView('admin/clients/index.html.twig', 
                    [
                         "roles"=>$roles,
                         "antenne" => $this->getUser()->getAntene()
                     ]);
                  
                }
                $this->successResponse("Liste des clients ", $link, $data);
            }else{
                $this->redirectToRoute('login');
            }
            

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }

    /**
     * @Route("/indexclientdash", name="index-clientdash", methods={"GET"})
     */
    public function indexclientdash()
    {
        $link="indexclientdash";

        try {
            
                //$clients = $this->em->getRepository(User::class)->findBy(['type' => 'CLIENT']);
                if($this->getUser()->getIsadmin()){
                    $clients = $this->em->getRepository(User::class)->findBy(['type' => 'CLIENT']);
                }else{
                    $clients = $this->em->getRepository(User::class)->findBy([
                        'type' => 'CLIENT',
                        'antene' => $this->getUser()->getAntene()
                    ]);
                }
                $roles=$this->em->getRepository(Role::class)->findAll();
                $entenes=$this->em->getRepository(Entene::class)->findAll();
                $typchambres = $this->em->getRepository(Typechambre::class)->findAll();
                $chambres = $this->em->getRepository(Chambre::class)->findAll();
                $tarif = $this->em->getRepository(Tarif::class)->findAll();

                $data = $this->renderView('admin/clients/clientdashboard.html.twig', [
                   "clients"=>$clients,
                    "roles"=>$roles,
                    "entenes"=>$entenes,
                    "typchambres" => $typchambres,
                    "chambres" => $chambres,
                    "tarifs" => $tarif,

                ]);
               

                $this->successResponse("Liste des clientsdash ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        // dd($this->result);
        return $this->json($this->result);
    }


    /**
     * @Route("/clientvue", name="clientvue", methods={"GET"})
     */
    public function clientvue()
    {
        $link="Client";
        

        try {
                $clients=$this->em->getRepository(User::class)->findAll();
               
              
                $data = $this->renderView('admin/clients/index.html.twig', 
                [ "clients"=>$clients]);
                $this->successResponse("Liste des clients ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }


    /**
     * @Route("/clientadd", name="client-add", methods={"POST"})
     */
    public function clientadd(Request $request){
        $link="indexclient";
        try {

            
            $nom =  $request->get('nom');
            $prenom =  $request->get('prenom');
            $datenais=  $request->get('datenaisance');
            $datenaisance = new \DateTime($datenais);
            //dd($datenaisance);
            $sex =  $request->get('sex');
            // $username =  $request->get('username');
            //$password =  $request->get('password');
            $cni =  $request->get('cni');
            $lieunaisance =  $request->get('lieunaisance');
            $etatcivil =  $request->get('etatcivil');
            $profession =  $request->get('profession');
            $nationalite =  $request->get('nationalite');
            $phone =  $request->get('phone');
            $adresse =  $request->get('adresse');
            // $type =  $request->get('type');
            $photo = $request->files->get("photo");
            //dd($photo);

            $user = new User();
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setDatenaisance($datenaisance);
            $user->setSexe($sex);
            $user->setIsadmin(false);
            $user->setSolde(0);
            
            if(empty($request->get("email"))){
                $user->setEmail($nom."".$prenom."@hotelapp.com");
            }else{
                $user->setEmail($request->get("email"));
            }
            
            $user->setCni($cni);
            $user->setUsername(trim($nom."".$this->getUser()->getAntene()->getAcronym()));
            $user->setPassword($this->passwordEncoder->encodePassword($user,"12345@abc"));
            $user->setLieunaisance($lieunaisance);
            $user->setEtatcivil($etatcivil);
            
            $user->setProfession($profession);
            $user->setNationalite($nationalite);
            $user->setPhone($phone);

            $user->setAdresse($adresse);
            $user->setType("CLIENT");
            $user->setAntene($this->getUser()->getAntene());
            
            if(!empty ($photo)){
                $path = md5(uniqid()).'.'.$photo->guessExtension();
                $photo->move($this->getParameter('Chambre'), $path);

                $user->setPhoto($path);
            }
            $user->setAntene($this->getUser()->getAntene());
            $this->em->persist($user);
            $this->em->flush();
            $this->setlog("AJOUTER","L'utilisateur ".$this->getUser()->getUsername().
            " a  ajouter le client ".$user->getNom(),"CLIENT",$user->getId());
            //dd($user);
            $this->successResponse("Client ajoutée ", $link);
        }catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return $this->json($this->result);
    }

    /**
    * @Route("deleteclient/{id}", name="delete-client")
    */
    public function deleteAction($id) {
        $link="indexclient";
        try{
            $em = $this->getDoctrine()->getManager();
            $client = $this->getDoctrine()->getRepository(User::class)->find($id);

            if (!$client) {
                throw $this->createNotFoundException(
                    'There are no articles with the following id: ' . $id
                );
            }
            $em->remove($client);
            $em->flush();
            $this->setlog("SUPPRIMER","L'utilisateur ".$this->getUser()->getUsername().
             " a  supprimer le client ".$client->getNom(),"CLIENT",$client->getId());
        //dd($user);
            $this->successResponse("client supprimé ", $link);
        }
        catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return new JsonResponse($this->result);
          
    }


    /**
      * @Route("/showclient/{id}", name="show-client", methods={"GET"})
      *
    */
    public function viewClient($id) {
        $link="vueclient";

        try {
           // $clients = $this->em->getRepository(User::class)->findBy(['type' => 'CLIENT']);
            $roles=$this->em->getRepository(Role::class)->findAll();
            $entenes=$this->em->getRepository(Entene::class)->findAll();
            $typchambres = $this->em->getRepository(Typechambre::class)->findAll();
            $chambres = $this->em->getRepository(Chambre::class)->findAll();
            $tarif = $this->em->getRepository(Tarif::class)->findAll();

            $transactions = $this->em->getRepository(Transaction::class)->findBy([
                'client' => $id
            ]);
            //dd($transactions);
            $client = $this->em->getRepository(User::class)->find($id);
			
            $reservations= $this->getDoctrine()->getRepository(Reservation::class)->findBy(['client' => $client]);
            
          
            $allocations = $this->em->getRepository(Allocation::class)->findBy(['occupant'=>$client]);

            $note = $this->em->getRepository(Note::class)->findBy(['clientnote'=>$id]);
            //dd($note);
            if (!$client) {
                throw $this->createNotFoundException(
                    'Aucun client pour l\'id: ' . $id
                );
            }
            $data = $this->renderView('admin/clients/view.html.twig', [
                'client' => $client,
                'reservations' => $reservations,
                'allocations' => $allocations,
                'transactions' => $transactions,
                //"clients"=>$clients,
                "roles"=>$roles,
                "entenes"=>$entenes,
                "typchambres" => $typchambres,
                "chambres" => $chambres,
                "tarifs" => $tarif,
                "notes" => $note,
               
            ]);
			
            $this->successResponse("vueclient ", $link, $data);


        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return $this->json($this->result);
    }   



    /**
     * @Route("/edit-client/{id}/edit", name="edit-client",methods={"GET"})
     */
    public function getDataEdit(int $id): Response
    {
        
      
        $link="client";
        try {
            $client = $this->em->getRepository(User::class)->find($id);
        
        $data = $this->renderView('admin/clients/edit.html.twig', [
            'client' => $client
           
        ]);
        $this->successResponse("vue edit des clients ", $link, $data);
   
    
    } catch (\Exception $ex) {
        $this->log($ex->getMessage(), $link);
    }
    return $this->json($this->result);
}

     /**
      * @Route("/editclient/edit/", name="aedit-client",methods={"POST"})
      *
      */
      public function editclient(Request $request): Response
    {
        $link="indexclient";
        try{
            $id = $request->get("id");
        
     
            $nom =  $request->get('nom');
            $prenom =  $request->get('prenom');
            $datenais=  $request->get('datenaisance');
            $datenaisance = new \DateTime($datenais);
            $sex =  $request->get('sex');
            $cni =  $request->get('cni');
            $lieunaisance =  $request->get('lieunaisance');
            $etatcivil =  $request->get('etatcivil');
            $profession =  $request->get('profession');
            $nationalite =  $request->get('nationalite');
            $phone =  $request->get('phone');
            $adresse =  $request->get('adresse');
            
            $photo = $request->files->get("photo");
            //dd($photo);
            $user = $this->em->getRepository(User::class)->find($id);

        
        
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setDatenaisance($datenaisance);
            $user->setSexe($sex);
            
            if(empty($request->get("email"))){
                $user->setEmail($nom."".$prenom."@hotelapp.com");
            }else{
                $user->setEmail($request->get("email"));
            }
        
            $user->setCni($cni);
            $user->setUsername(trim($nom."".$prenom));
            $user->setPassword($this->passwordEncoder->encodePassword($user,"12345@abc"));
            $user->setLieunaisance($lieunaisance);
            $user->setEtatcivil($etatcivil);
            
            $user->setProfession($profession);
            $user->setNationalite($nationalite);
            $user->setPhone($phone);
            $user->setAdresse($adresse);

            if(!empty ($photo)){
                $path = md5(uniqid()).'.'.$photo->guessExtension();
                $photo->move($this->getParameter('Chambre'), $path);

                $user->setPhoto($path);
            }
                
        

            $this->em->persist($user);
            $this->em->flush($user);
            $this ->addFlash('success','client modifié avec succes');
            $this->setlog("MODIFIER","L'utilisateur ".$this->getUser()->getUsername().
            " a  modifier le client ".$user->getNom(),"CLIENT",$user->getId());

            $this->successResponse("client Modifier !",$link);  
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);

        }



    /**
     * @Route("/printclient", name="print-client")
     */
    public function printclient(){
        $link="client";
        $antene = $this->getUser()->getAntene();
        if($this->getUser()->getIsadmin()){
            $clients = $this->em->getRepository(User::class)->findBy(['type' => 'CLIENT']);
        }else{            
            $clients = $this->em->getRepository(User::class)->findBy(['type' => 'CLIENT', 'antene'=>$antene]);
        }
            
            
            //dd($antene);
            $template = $this->renderView('admin/print/printclient.pdf.twig', [
                "clients" => $clients,
                "antene" => $antene,
            ]);
        
       // dd($this->result);
       return $this->returnPDFResponseFromHTML($template, "Liste des clients", "L"); 
    }

    /**
     * @Route("/opperation", name="opperation", methods={"POST"}) 
     */
    public function opperation(Request $request){

        $link="showclient/";
        try {
            $montan =  $request->get('montant');
            $clientid =  $request->get('client');
            if($montan < 100){
                $montant = null;
            }else{
                $montant = $montan;
            }
            if($montant){
                
                $client = $this->em->getRepository(User::class)->find($clientid); 
                
                $solde = $client->getSolde();
                $newsolde = $solde + $montant;
            
                //return $this->redirectToRoute('show-client',[]);
                
                $client->setSolde($newsolde);

                $transaction = new Transaction();
                $transaction->setType("Créditer");
                $transaction->setMontant($montant);
                $transaction->setCreatedat(new \DateTime("now"));
                $transaction->setCreatedby($this->getUser());
                $transaction->setClient($client);

                $this->em->persist($transaction);
                $this->em->flush($transaction);

                $this->em->persist($client);
                $this->em->flush($client);
                $this->setlog("Créditer le compte","L'utilisateur ".$this->getUser()->getUsername().
                " a Créditer le client de ".$client->getNom(),"CLIENT",$client->getId());

                $this ->addFlash('success',"Vous avez Créditer le compte de ".$client->getNom()." avec succes"); 
                
            }else{
                $this ->addFlash('danger',"Le montant ne peut être null ou inférieur à 100Frs "); 
                
            }
            $this->successResponse("Opération d'acompte ",$link.$clientid);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }

    /**
     * @Route("/note", name="note-client", methods={"POST"}) 
     */
    public function note(Request $request){

        $link="showclient/";
        try {
            $content =  $request->get('content');
            $clientid =  $request->get('client');
            if($content){
                
                $client = $this->em->getRepository(User::class)->find($clientid); 
                
                //return $this->redirectToRoute('show-client',[]);
                $not = new Note();
                $not->setContent($content);
                $not->setCreatedat(new \DateTime("now"));
                $not->setCreatedby($this->getUser());
                $not->setClientnote($client);

                $this->em->persist($not);
                $this->em->flush($not);
                $this->setlog("Creation d'une note","L'utilisateur ".$this->getUser()->getUsername().
                " a noté le client de ".$client->getNom(),"CLIENT",$client->getId());

                $this ->addFlash('notesuccess',"Vous avez Noté le client ".$client->getNom()." avec succes"); 
                
            }else{
                $this ->addFlash('notedanger',"La note ne peut être vide il faut saisir quelque chose"); 
                
            }
            $this->successResponse("Opération d'acompte ",$link.$clientid);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
        return new JsonResponse($this->result);
    }

}
