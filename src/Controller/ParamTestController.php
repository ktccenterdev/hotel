<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParametreRepository;
use App\Entity\Parametre;
use Symfony\Component\HttpFoundation\JsonResponse;

class ParamTestController extends DefaultController
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em=$em;
    }

    /**
     * @Route("/indextest", name="index-test", methods={"GET"})
     */
    public function indextest()
    {
        $link="indextest";


        try {
                  $params = $this->em->getRepository(Parametre::class)->findAll();  
/*                   $data = $this->renderView('admin/test/test.html.twig', [
 */     
$data = $this->renderView('admin/test/test.html.twig', [
  
/* $data = $this->renderView('admin/parametre/index.html.twig', [
 */    "params"=>$params

                ]);
                $this->successResponse("indextestParametre de l'hotel ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }
     
        return $this->json($this->result);
    }


    
     /**
     * @Route("/paramadd", name="paramadd", methods={"POST"})
     */
    public function paramadd(Request $request){
        try {
            $nom = $request->get("nom");
            $description = $request->get("description");
            $site = $request->get("site");
            $email = $request->get("email");
            $bp = $request->get("bp");
            $pourcentagereservation= $request->get("pourcentagereservation");
       
            $parametre= new Parametre();
            $parametre->setNom($nom);
            $parametre->setDescription($description);
            $parametre->setSite($site);
            $parametre->setEmail($email);
            $parametre->setBp($bp);
            $parametre->setPourcentagereservation($pourcentagereservation);

            $this->em->persist($parametre);
            $this->em->flush();
            $this->setlog("AJOUTER","Le Parametre ".$this->getUser()->getUsername().
            " a ajouter le Magasin ".$parametre->getNom(),"PARAMETRE",$parametre->getId());
            $this->successResponse("Parametre ajoutée ","paramadd"); 



    
    }catch (\Exception $ex) {
        $this->log($ex->getMessage(), "paramadd");
    } 
    return $this->json($this->result);
    }
/////

 /**
     * @Route("/paramedit/{id}/edit", name="paramedit",methods={"GET"})
     */
    public function getDataEdit(int $id): Response
    {
        $pp = null;
        $res = $this->em->getRepository(Parametre::class)->findBy(
            [
                'user' => $this->getUser()
            ]);
        if($res){
            $pp = $res[0];
        }
     

      

        return $this->render('admin/test/update.html.twig', [
            
            'user' => $this->getUser(),
            'photo' => $pp
        ]);
    }

////

/**
     * @Route("/parametreedit/edit/", name="parametre_edit",methods={"POST"})
     */
    /* public function editAnnouncement(Request $request): Response
    {
        $formulaire = $request->request->all();
        $id = $request->get("id");
        
        $pp = null;
        $res = $this->em->getRepository(Parametre::class)->findBy([
                'user' => $this->getUser(),
              
            ]);
        if($res){
            $pp = $res[0];
        }

        // get elements of formulaire OneByOne
            $nom = $request->get("nom");
            $description = $request->get("description");
            $site = $request->get("site");
            $email = $request->get("email");
            $bp = $request->get("bp");
            $pourcentagereservation= $request->get("pourcentagereservation");
        //dd($exigence);

        //buld object to send in DB
        $parametre = $this->em->getRepository(Parametre::class)->find($id);

        //$announcement = new Announcement();
        $parametre->setNom($nom);
            $parametre->setDescription($description);
            $parametre->setSite($site);
            $parametre->setEmail($email);
            $parametre->setBp($bp);
            $parametre->setPourcentagereservation($pourcentagereservation);
        //dd($announcement);

        $this->em->persist($parametre);
        $this->em->flush($parametre);

        return $this->redirectToRoute('indextest',[
            'id' => $id,
            'user' => $this->getUser(),
           
        ]);
    }
 */





}
