<?php

namespace App\Controller;

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



class EmployeController extends DefaultController
{
   /*  public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em=$em;
        $this->passwordEncoder=$passwordEncoder;
    } */

    /**
     * @Route("/employe", name="index-employe", methods={"GET"})
     */
    public function indexemploye()
    {
        $link="employe";

        try {
            $user = $this->getUser();
            if($user->getIsadmin()){
                $employe = $this->em->getRepository(User::class)->findBy([
                    'type' => 'EMPLOYE',
                    'isdeleted' => false
                ]);
            }else{
                $employe = $this->em->getRepository(User::class)->findBy([
                    'type' => 'EMPLOYE',
                    'isdeleted' => false,
                    'antene' => $user->getAntene()
                ]);
            }
            $users = $this->em->getRepository(User::class)->findAll();
           
            $entenes = $this->em->getRepository(Entene::class)->findAll();
            $roles = $this->em->getRepository(Role::class)->findAll();
            
            //dd($employe);
            $data = $this->renderView('admin/employe/index.html.twig', [
                "entenes" => $entenes,
                "roles" => $roles,
                "users" => $users,
                "employes" => $employe
            ]);
            $this->successResponse("Liste des employés ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }
    
    /**
     * @Route("/employeadd", name="add-employe", methods={"POST"})
     */
    public function employeadd(Request $request)
    {
        $link="employe";
        $user = $this->getUser();
       
        //dd($antene);

       

       
       
       
        try {
            $username =  $request->get('username');
            $utilisateur = $this->em->getRepository(User::class)->findBy(
                ['username' => $username]
            );
            if(!$utilisateur){
                
                $nom =  $request->get('nom');
                $prenom =  $request->get('prenom');
                $date =  $request->get('date');
                //$dat =  new \DateTime($date); new \DateTime('now');
                $datetime = new \DateTime($date);

                $lieu =  $request->get('lieu');
                $sexe =  $request->get('sexe');
                $cni =  $request->get('cni');
                $etatcivil =  $request->get('etatcivil');
                $nationalite =  $request->get('nationalite');
                $email =  $request->get('email');
                $phone =  $request->get('phone');
                $adresse =  $request->get('adresse');

               
                if($user->getIsadmin()){
                    $identenne =  $request->get('entenne');
                    $entenne = $this->em->getRepository(Entene::class)->find($identenne);
    
                }else {
                    $entenne = $this->getUser()->getAntene();
                }
                

                $rol =  $request->get('role');
                $role = $this->em->getRepository(Role::class)->find($rol);
                //dd($role);
                $photo = $request->files->get("photo");
            
                //dd($photo);
                $passw =  $request->get('passw');

                $employe = new User();
                $employe->setUsername($username);
                $employe->setNom($nom);
                $employe->setPrenom($prenom);
                $employe->setDatenaisance($datetime);
                $employe->setLieunaisance($lieu);
                $employe->setSexe($sexe);
                $employe->setCni($cni);
                $employe->setEtatcivil($etatcivil);
                $employe->setNationalite($nationalite);
                $employe->setEmail($email);
                $employe->setPhone($phone);
                $employe->setAdresse($adresse);
                $employe->setAntene($entenne);
                $employe->setIsadmin(false);
                $employe->setRole($role);
                $employe->setType("EMPLOYE");
                //$employe->setPassword($passw);
                //dd($employe);
               
                $employe->setPassword(
                    $this->passwordEncoder->encodePassword(
                        $employe,$passw
                    )
                );
                 if(!empty ($photo)){
                    $path = md5(uniqid()).'.'.$photo->guessExtension();
                    $photo->move($this->getParameter('Employes'), $path);
                    $employe->setPhoto($path);
                }
                $employe->setIsdeleted(false);
                
                $this->em->persist($employe);
                $this->em->flush();
                
                $this->setlog("AJOUTER","L'utilisateur ".$this->getUser()->getUsername().
                " a ajouter un employee ".$employe->getNom(),"EMPLOYEE",$employe->getId()); 
                        
                $this->successResponse("Employé ajouté ",$link);
            }else{
                $this->setlog("ECHEC D'AJOUT","L'utilisateur ".$this->getUser()->getUsername().
                " a voulus ajouter un employee qui existe dejà".$username,"EMPLOYEE",$utilisateur[0]->getId()); 

                $this ->addFlash('409', "L'utilisateur ".$username." existe déjà.");  
                return $this->redirectToRoute('index-employe',[]);
            }
           
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), "employe");
        }
        return new JsonResponse($this->result);
        
    }

    /**
      * @Route("/showemploye/{id}", name="showemploye")
      *
    */
    public function viewEmployee($id) {

        $link="employe";

        try {
            $entenes = $this->em->getRepository(Entene::class)->findAll();
            $employe = $this->getDoctrine()->getRepository(User::class)->find($id);
            //dd($employe);
            if (!$employe) {
                throw $this->createNotFoundException(
                    "Aucun employe pour l'id: " . $id
                );
            }
            $this->setlog("Visualisation","L'utilisateur ".$this->getUser()->getUsername().
                "a vu les détails de l'employee ".$employe->getNom(),"EMPLOYEE N-",$employe->getId()); 
            $data = $this->renderView('admin/employe/view.html.twig', [
                "employe" => $employe,
                "entenes" => $entenes
            ]);
            $this->successResponse("Liste des employes ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        
        return $this->json($this->result);

    }  

    
    /**
    * @Route("deleteemp/{id}", name="deleteemp")
    *
    */
    public function deleteAction($id) {
        $link = "employe";
        try{
            $employe = $this->getDoctrine()->getRepository(User::class)->find($id);
            if (!is_null($employe)) {
                if(count($employe->getClienttransactions()) === 0){
                    $this->em->remove($employe);
                    $this->em->flush();
                    $this->setlog("Visualisation","L'utilisateur ".$this->getUser()->getUsername().
                    " a suprimé l'employé ".$employe->getNom(),"EMPLOYEE N-",$employe->getId());
                    $this->successResponse("employe supprimé ", $link);
                }else{
                    $this->log("Impossible de supprimer cette employee, des données y sont liées.", $link);
                }
            }else{
                $this->log("Employee  introuvable.", $link);
            }
           
        } 
        catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        } 
        return new JsonResponse($this->result);

    }



    /**
     * @Route("/edit-employe/{id}/edit", name="edit-employe",methods={"GET"})
     * 
     */
    public function getDataEdit(int $id): Response
    {

        $link="getDataEdit";
        try {
            $entenes = $this->em->getRepository(Entene::class)->findAll();
            $user = $this->em->getRepository(User::class)->find($id);
            //dd($user);
            $data = $this->renderView('admin/employe/edit.html.twig', [
                'user' => $user,
                "entenes" => $entenes
            ]);
            $this->successResponse("vue edit de l'employé", $link, $data);
    
            
        } catch (\Exception $ex){
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }

     /**
      * @Route("/edit-employe/edit", name="editemploye",methods={"POST"})
      *
      */
    public function editemploye(Request $request): Response
    {
        $link="employe";

        try {
            $id = $request->get("id");
            $employe = $this->em->getRepository(User::class)->find($id);

            //$announcement = new Announcement();
        
            $employe->setNom($request->get("nom"));
            $employe->setPrenom($request->get("prenom"));
            $employe->setUsername($request->get("username"));
            $employe->setDatenaisance(new \DateTime( $request->get("date")));
            $employe->setLieunaisance($request->get("lieu"));
            $employe->setSexe($request->get("sexe"));
            $employe->setCni($request->get("cni"));
            $employe->setEtatcivil($request->get("etatcivil"));
            $employe->setNationalite($request->get("nationalite"));
            $employe->setEmail($request->get("email"));
            $employe->setPhone($request->get("phone"));
            $employe->setAdresse($request->get("adresse"));


            $employe->setAntene($this->getUser()->getAntene());
            
            $photo =  $request->files->get('photo');
            if(!empty($photo) ){
                $path = md5(uniqid()).'.'.$photo->guessExtension();
                $photo->move($this->getParameter('Employes'), $path);

                $employe->setPhoto($path);
                
            }

            $this->em->persist($employe);
            $this->em->flush($employe);
            $this->setlog("Modification","L'utilisateur ".$this->getUser()->getUsername().
                " a modifier l'employe ".$employe->getNom(),"EMPLOYEE N-",$employe->getId());
            return $this->redirectToRoute("index-employe");

        } catch (\Exception $ex){
            $this->log($ex->getMessage(), $link);
        }
        return $this->json($this->result);
    }


    /**
     * @Route("/printemploye", name="print-employe")
     */
    public function printemploye(){
        $link="employe";
            $antene = $this->getUser()->getAntene();
            $user = $this->getUser();
            if($user->getIsadmin()){
                $employe = $this->em->getRepository(User::class)->findBy(['type' => 'EMPLOYE']);
            }else{
                $employe = $this->em->getRepository(User::class)->findBy([
                    'type' => 'EMPLOYE',
                    'antene' => $user->getAntene()
                ]);
            }
            $this->setlog("Impression","L'utilisateur ".$this->getUser()->getUsername().
                "a imprimer la liste des employees ","Liste des EMPLOYEES",);

            $template = $this->renderView('admin/print/printemploye.pdf.twig', [
                "employe" => $employe,
                "antene" => $antene
            ]);
        
       // dd($this->result);
       return $this->returnPDFResponseFromHTML($template, "Liste des employes"); 
    }



}
