<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Module;
use App\Entity\Role;
use App\Entity\Action;
use App\Entity\User;
use App\Entity\ActionRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;



class UserController extends DefaultController
{
   


    /**
     * @Route("/changepassword", name="changepassword", methods={"POST"})
     */
    public function changepassword(Request $request)
    {   
        $user=$this->getUser();
        $ancienpassword =  $request->get('ancienpassword');
        $newpassword =  $request->get('newpassword');
      
        if($ancienpassword != $newpassword){
            $this->log("Les 2 mots de passe ne sont pas identiques.", "indexrole","alert");
        }else{
            
            try {
                
                $user->setPassword($this->passwordEncoder->encodePassword($user,$newpassword));
                $this->em->flush($user);
                return $this->redirect("/login");
            } catch (\Exception $e) {
                $this->log("Un problème est survenu", "indexrole","alert");
                return new JsonResponse($this->result);
            }
        }
        
    }


    /**
     * @Route("/updateroleuser", name="updateroleuser", methods={"PUT"})
     */
    public function updateroleuser(Request $request)
    {
        $link = "utilisateur-index";
        $userId =  intval($request->get('user'));
        $roleId =  intval($request->get('role'));
        $username =  $request->get('username');
        $isadmin =  boolval($request->get('superadmin'));
        try {
            if($username){
                if($userId){
                    if($roleId){
                        $role=$this->em->getRepository(Role::class)->find($roleId);
                        if($role){
                            $user=$this->em->getRepository(User::class)->find($userId);
                            if($user){
                                $checkUsername=$this->em->getRepository(User::class)->findOneBy(['username'=>$username]);
                                if(is_null($checkUsername) || $checkUsername->getId() == $user->getId()){
                                    $user->setRole($role);
                                    $user->setUsername($username);
                                    $user->setIsadmin($isadmin);
                                    $this->em->flush($user);
                                    $this->setlog("MODIFIER","L'utilisateur ".$this->getUser()->getUsername()." a modifié un utilisateur  ".$user-> getNom(),"USER",$user->getId()); 
                                    $this->successResponse("Utilisateur editer","utilisateur-index");  
                                }else{
                                    $this->log("Un autre utilisateur possede deja ce username.", $link);
                                }
                            }else{
                                $this->log("Utilisateur introuvable", $link);
                            }
                        }else{
                            $this->log("Role introuvable", $link);
                        }
                    }else{
                        $this->log("Rôle obligatoire", $link);
                    }
                }else{
                    $this->log("Selectionner un utilisateur", $link);
                }
            }else{
                $this->log("Username obligatoire", $link);
            }
            
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), "utilisateur-index");
        }
        return new JsonResponse($this->result);
    }

    /**
     * @Route("/printuser", name="print-user")
     */
    public function printuser(){
        $link="utilisateur-index";
            $antene = $this->getUser()->getAntene();
            $utilisateurs=$this->em->getRepository(User::class)->findAll();
            //dd($antene);
            $template = $this->renderView('admin/print/printuser.pdf.twig', [
                "utilisateurs" => $utilisateurs,
                "antene" => $antene,
            ]);
        
       // dd($this->result);
       return $this->returnPDFResponseFromHTML($template, "Liste des utilisateurs", "L"); 
    }

        /**
     * @Route("/useradd", name="user-add", methods={"GET"})
     */
    // public function useradd(Request $request)
    // {
    //     $user=new User();
    //     $user->setUsername("admin");
    //     $user->setPassword("admin");
    //     $actions=$this->em->getRepository(Role::class)->find(9);
    //     $user->setRole($actions);
    //     $this->em->persist($user);
    //     $this->em->flush();
    //     $this->setlog("AJOUTER","L'utilisateur ".$this->getUser()->getUsername().
    //     "a ajouter un administrateur  ".$user-> getUsername(),"USER",$user->getId()); 
    // }

      /**
     * @Route("/utilisateur-index", name="utilisateur-index", methods={"GET"})
     */
    public function indexutilisateur(Request $request)
    {
        $link="utilisateur-index";
        $user = $this->getUser();        
        try {
            $roles = $this->em->getRepository(Role::class)->findAll();
            if($user){
                if($user->getIsadmin()){
                    $listeUtilisateurs = array();
                    $antennes = $this->getAllAntennes();
                    foreach ($antennes as $antenne) {
                        $utilisateurs=$this->em->getRepository(User::class)->findBy(['type'=>'EMPLOYE', 'antene'=>$antenne]);
                        $listeUtilisateurs[$antenne->getId()] = $utilisateurs;
                    }
                    $data = $this->renderView('admin/users/utilisateuradmin.html.twig', 
                    ["utilisateurs"=>$listeUtilisateurs, "antennes"=>$antennes, "roles"=>$roles]);
                }else{
                    $utilisateurs=$this->em->getRepository(User::class)->findBy(['type'=>'EMPLOYE', 'antene'=>$user->getAntene()]);
                    $data = $this->renderView('admin/users/utilisateur.html.twig', ["utilisateurs"=>$utilisateurs, "roles"=>$roles]);
                }
                $this->successResponse("Liste des utilisateurs", $link, $data);
            }else{
                return $this->redirectToRoute("login");
            }
                
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       return $this->json($this->result);
    }

    /**
     * @Route("/utilisateurdelete", name="utilisateur-delete", methods={"DELETE"})
     */
    public function delete(Request $request)
    {
        $link="utilisateurdelete";
        $id = intval($request->get("id"));
        try {
            $user = $this->em->getRepository(User::class)->find($id);                
            if(!is_null($user)){
                if(count($user->getAllocations()) === 0 && count($user->getSortieFinancieres())===0 && !$user->getIsadmin()){
                    $this->em->remove($user);
                    $this->em->flush();
                    $this->successResponse("Utilisateur Supprimé ",$link);
                }else{
                    $this->log("Impossible de supprimer cet utilisateur, des informations y sont liées.", $link);
                }                 
            }else{
                $this->log("Utilisateur inexistant, veuillez sélectionner un autre.", $link);
            }            
            return new JsonResponse($this->result);
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
            return new JsonResponse($this->result, 400);
        }
    }

}
