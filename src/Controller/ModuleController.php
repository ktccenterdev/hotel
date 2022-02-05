<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Module;
use App\Entity\Action;
use App\Entity\Role;
use  App\Entity\ActionRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class ModuleController extends DefaultController
{
   

    /**
     * @Route("/indexmodule", name="index-module", methods={"GET"})
     */
    public function indexmodule(Request $request)
    {

        $link="Module";
        try {
            //$sbf=$fknd;
                $modules=$this->em->getRepository(Module::class)->findAll();
                $data = $this->renderView('admin/module/index.html.twig', ["modules"=>$modules]);
                $this->successResponse("Liste des modules ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
       // dd($this->result);
        return $this->json($this->result);
    }




    ////////////////for developeur  
        /**
         * @Route("/modulecle-add", name="modulecle-add", methods={"GET"})
         */
        public function modulecleadd(Request $request)
        {

            $link="Module";
            try {
                    $modules=$this->em->getRepository(Module::class)->findAll();
                    $data = $this->renderView('admin/module/modulecle.html.twig', ["modules"=>$modules]);
                    $this->successResponse("Liste des modules cles", $link, $data);

            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }


        /**
         * @Route("/addfordev-add", name="addfordev-add", methods={"POST"})
         */
        public function addfordevadd(Request $request)
        {

            $link="index-role";
            try {

                $moduleaction= new Action();
                $moduleaction->setNom($request->get('nom'));
                $moduleaction->setIsdefault($request->get('isdefault'));
                $moduleaction->setCle($request->get('cle'));
                $moduleaction->setVisible(true);
                $moduleaction->setCreateAt(new \DateTime("now"));
                $moduleaction->setDescription($request->get('description'));
                $moduleaction->setModule($this->em->getRepository(Module::class)->find($request->get('module')));
                //dd($moduleaction);
                $this->em->persist($moduleaction);
                $this->em->flush();
                $this->setlog("AJOUTER","L'utilisateur ".$this->getUser()->getUsername().
                "a ajouter le module  ".$moduleaction->getNom(),"ACTION",$moduleaction->getId()); 
                $roles=$this->em->getRepository(Role::class)->findAll();
                
                foreach ($roles as &$role) {
                    $actionrole= new ActionRole();
                    $actionrole->setRole($role);
                    $actionrole->setAction($moduleaction);
                    $actionrole->setEtat(0);
                    $this->em->persist($actionrole);
                    $this->em->flush();
                } 
                //dd($moduleaction);
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            //return $this->json($this->result);
            return $this->redirectToRoute($link,[]);
        }
    /////end for developer


    /**
     * @Route("/moduleadd", name="module-add", methods={"POST"})
     */
    public function moduleAdd(Request $request)
    {
        try {
            $intitule =  $request->get('nom');
            $description =  $request->get('description');
            $module=new Module();
            $module->setNom($intitule);
            $module->setDescription($description);
            $this->em->persist($module);
            $this->em->flush();           
            $this->successResponse("Module ajoutée ","indexmodule");  
        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), "indexmodule");
        }
        return new JsonResponse($this->result);
        
    }


}
