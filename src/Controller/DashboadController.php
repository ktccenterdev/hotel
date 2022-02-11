<?php

namespace App\Controller;

use App\Entity\Chambre;
use App\Entity\Entene;
use App\Entity\Typechambre;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DashboadController extends DefaultController
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em=$em;
    }

    /**
     * @Route("/indexdashboad", name="index-dashboad", methods={"GET"})
     */
    public function indexdashboad()
    {
        
        $link="index-dashboad";
        $userg = $this->getUser();
        if($userg){
            try {
                if($this->getUser()->getIsadmin()){
                    $entene = $this->em->getRepository(Entene::class)->findAll();
                    $typec = $this->em->getRepository(Typechambre::class)->findAll();
                    $chambre = $this->em->getRepository(Chambre::class)->findAll();
                }else{
                    $entene = $this->em->getRepository(Entene::class)->findBy([
                        'id' => $this->getUser()->getAntene()->getId()
                    ]);
                    $typec = $this->em->getRepository(Typechambre::class)->findBy([
                        'antene'=> $this->getUser()->getAntene()
                    ]);
                    $chambre = $this->em->getRepository(Chambre::class)->findBy([
                        'entene'=> $this->getUser()->getAntene()
                    ]);
                }
                
                $nbentene = count($entene);
                $nbchambre = count($chambre);

                $data = $this->renderView('admin/dashboad/index.html.twig', [
                    "nbentene" => $nbentene,
                    "nbchambre" => $nbchambre,
                    "entenes" => $entene,
                    "typecs" => $typec,
                ]);
                $this->successResponse("Dahboard", $link, $data);

            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }
            return $this->json($this->result);
        }else{
            return $this->redirectToRoute('login');
        }
    }
}
