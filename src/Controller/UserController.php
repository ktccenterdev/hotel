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
        // $array = array(
        //     "old" => $this->passwordEncoder->encodePassword($user,$newpassword),
        //     "bar" => $user->getPassword(),
        // );
        // dd($array);
        if($ancienpassword != $newpassword){
            $this->log("Les 2 mots de passe ne sont pas identiques.", "indexrole","alert");
        }else{
            
            try {
                
                $user->setPassword($this->passwordEncoder->encodePassword($user,$newpassword));
                $this->em->flush($user);
                return $this->redirect("/login");
            } catch (Exception $e) {
                $this->log("Un problème est survenu", "indexrole","alert");
                return new JsonResponse($this->result);
            }
        }
        
    }


    /**
     * @Route("/updateroleuser", name="updateroleuser", methods={"POST"})
     */
    public function updateroleuser(Request $request)
    {
        $user =  $request->get('user');
        $role =  $request->get('role');
        $admin =  $request->get('superadmin');
        $isadmin = false;
        try {
            $user=$this->em->getRepository(User::class)->find($user);
            $role=$this->em->getRepository(Role::class)->find($role);
            $user->setRole($role);
            if($admin){
                $isadmin = true;
            }
            //dd($isadmin);
            $user->setIsadmin($isadmin);
            $this->em->flush($user);
            $this->setlog("AJOUTER","L'utilisateur ".$this->getUser()->getUsername().
            "a ajouter un utilisateur  ".$user-> getNom(),"USER",$user->getId()); 
            $this->successResponse("Utilisateur editer","utilisateur-index");  
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
}
