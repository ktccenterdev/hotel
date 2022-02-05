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

class RoleController extends DefaultController
{
    /**
     * @Route("/indexrole", name="index-role", methods={"GET"})
     */
    public function indexrole()
    {

        $link="Role";
        try {
                $roles=$this->em->getRepository(Role::class)->findAll();
                $data = $this->renderView('admin/users/role.html.twig', ["roles"=>$roles]);
                $this->successResponse("Liste des roles ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }


    /**
     * @Route("/roleadd", name="role-add", methods={"POST"})
     */
    public function moduleAdd(Request $request)
    {
        try {
            $intitule =  $request->get('nom');
            $description =  $request->get('description');
            $role=new Role();
            $role->setNom($intitule);
            $role->setDescription($description);
            $this->em->persist($role);
            $this->em->flush(); 
            $this->setlog("AJOUTER","L'utilisateur ".$this->getUser()->getUsername().
            " a ajouter le role  ".$role->getNom()," ROLE",$role->getId());

            $actions=$this->em->getRepository(Action::class)->findAll();
            
            foreach ($actions as &$action) {
                $roleaction=new ActionRole();
                $roleaction->setAction($action);
                $roleaction->setRole($role);
                $roleaction->setEtat($action->getIsdefault());
                $this->em->persist($roleaction);
                $this->em->flush(); 
            }         
            $this->successResponse("Role ajouté ","indexrole");  
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), "indexrole");
        }
        return new JsonResponse($this->result);
        
    }

    /**
     * @Route("/useradd", name="user-add", methods={"GET"})
     */
    public function useradd(Request $request)
    {
        $user=new User();
        $user->setUsername("admin");
        $user->setPassword("admin");
        $actions=$this->em->getRepository(Role::class)->find(9);
        $user->setRole($actions);
        $this->em->persist($user);
        $this->em->flush();
        $this->setlog("AJOUTER","L'utilisateur ".$this->getUser()->getUsername().
        "a ajouter un administrateur  ".$user-> getUsername(),"USER",$user->getId()); 
    }


    

    /**
     * @Route("/addorremovedroit", name="add-or-removedroit", methods={"PUT"})
     */
    public function addOrremovedroit(Request $request){
        $id =  $request->get('id');
        $actionrole=$this->em->getRepository(ActionRole::class)->find($id);
        //dd($request->get('etat'));
        if($request->get('etat')=="true"){
            $actionrole->setEtat(1);
        }else{
            $actionrole->setEtat(0);
        }
        
        $this->em->flush($actionrole);
        //dd($actionrole);
         
    }

    /**
     * @Route("/indexdroit", name="index-droit", methods={"GET"})
     */
    public function indexdroit(Request $request)
    {

        $id =  $request->get('id');
        $link="Role";
        try {
                $role=$this->em->getRepository(Role::class)->find($id);
                //$droits=$this->em->getRepository(ActionRole::class)->findBy(['role' => $id]);
                $modules=$this->em->getRepository(Module::class)->findAll();
                
                //$listeroles=$this->em->getRepository(ActionRole::class)->getactionofroleByModule(8);
                //dd($role);
                $tableauaction=array();
                foreach ($modules as &$module) {
                    $droitmodule=array();
                    foreach ($module->getActions() as &$action) {
                       $action=$this->em->getRepository(ActionRole::class)->findOneBy(['role' => $id,"action"=>$action->getId()]);
                       array_push($droitmodule,$action);
                    }
                    //dd($droitmodule);
                    array_push($tableauaction,array("module"=>$module->getNom(), "action"=>$droitmodule));
                    
                } 
                //dd($tableauaction);
                $data = $this->renderView('admin/users/droit.html.twig', ["role"=>$role,"tableauaction"=>$tableauaction]);
                $this->successResponse("Liste des droits du role ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       return $this->json($this->result);
    }

    /**
     * @Route("/utilisateur-index", name="utilisateur-index", methods={"GET"})
     */
    public function indexutilisateur(Request $request)
    {

        $id =  $request->get('id');
        $link="utilisateur-index";
        try {
                $utilisateurs=$this->em->getRepository(User::class)->findAll();
                $roles=$this->em->getRepository(Role::class)->findAll();

                $data = $this->renderView('admin/users/utiliteur.html.twig', ["roles"=>$roles,"utilisateurs"=>$utilisateurs]);
                $this->successResponse("Liste des utilisateurs", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       return $this->json($this->result);
    }

}
