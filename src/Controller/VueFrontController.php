<?php

namespace App\Controller;

use App\Entity\Entene;
use App\Entity\Chambre;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VueFrontController extends BaseController
{
    
     /**
     * @Route("/", name="indexfront", methods={"GET"})
     */
    public function indexfront()
    {
        
        $entenes=$this->em->getRepository(Entene::class)->findAll();
        
        return $this->render('intro/index.html.twig', [
            "entenes" => $entenes
        ]); 
    }

   /**
     * @Route("/home", name="home", methods={"GET"})
     */
    public function test()
    {
        return $this->render('admin/vuefront/index.html.twig', []);

    }
      
      /**
     * @Route("/french", name="french", methods={"GET"})
     */
    public function index()
    {
        $entenes=$this->em->getRepository(Entene::class)->findAll();
        return $this->render('intro/french.html.twig', [
            "entenes" => $entenes
        ]);

    }

    /**
     * @Route("/myantenne/{id}", name="myantenne", methods={"GET"})
     */
    public function myantenne($id)
    {
        $entene=$this->em->getRepository(Entene::class)->find($id);

        $chambre=$this->em->getRepository(Chambre::class)->findBy(
            ["entene"=>$entene]);
        //dd($chambre);
        return $this->render('intro/myantenne.html.twig', [
            "entene" => $entene,
            "chambres" => $chambre
        ]);

    }
      

    /**
     * @Route("/translate", name="translate", methods={"GET"})
     */
   /*  public function translate(TranslatorInterface $translator)
    {
        $translated = $translator->trans('Symfony is great');
        $translated1 = $translator->trans('Acceuil');
        //dd( $translated1);
        $entenes=$this->em->getRepository(Entene::class)->findAll();
        
        return $this->render('intro/basefront.html.twig', [
            "entenes" => $entenes,
            "translated1"=>$translated1,

        ]); */
        /* $response = new Response($this->twig->render('intro/index.html.twig', []));  */
       
        //dd($translated);
        
    /* }  */   
               

      /**
       * @Route("/change_locale/{locale}", name="change_locale")
       */
      public function changelocale($locale, Request $request){
        //dd($locale);
         $request->getSession()->set('_locale',$locale);

          return $this->redirect($request->headers->get('referer'));

       

      }
    


}
