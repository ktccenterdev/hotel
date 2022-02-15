<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Entene;
use App\Entity\Chambre;
use App\Entity\Reservation;
use App\Entity\Allocation;
use App\Entity\Tarif;
use App\Entity\Typechambre;
use App\Controller\DefaultController;
use App\Entity\ContactExterneSite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use App\Entity\Sms;


class NewsletterController extends DefaultController
{
     /**
     * @Route("/indexnewsletter", name="index-newsletter", methods={"GET"})
     */
    public function index()
    {
        $link="newsletter";

        try { 
            $data = $this->renderView('admin/newsletter/index.html.twig', []);
            $this->successResponse("Liste des chambres ", $link, $data);

        } catch (\Exception $ex) {
            $this->log($ex->getMessage(), $link);
        }

        return $this->json($this->result);
    }


    /**
     * @Route("/clientnewsletter", name="client-newsletter", methods={"GET"})
     */
        public function client()
        {
            $link="newsletter";

            try { 
                $typchambres = $this->em->getRepository(Typechambre::class)->findAll();
                $allocations = $this->em->getRepository(Allocation::class)->findAll();
                //dd($allocations);
                $chambres = $this->em->getRepository(Chambre::class)->findAll();
                $tarif = $this->em->getRepository(Tarif::class)->findAll();
                
                $clients = $this->em->getRepository(User::class)->findBy(
                    ['type' => "CLIENT"]);
                $data = $this->renderView('admin/newsletter/client.html.twig', [
                    "typchambres" => $typchambres,
                    "chambres" => $chambres,
                    "tarifs" => $tarif,
                    "allocations" => $allocations,
                    "clients" => $clients
                ]);   
                $this->successResponse("Liste des newsletterclient ", $link, $data);

            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }

            return $this->json($this->result);
        }

    /**
     * @Route("/employenewsletter", name="employe-newsletter", methods={"GET"})
     */
        public function employe()
        {
            $link="newsletter";

            try { 
                $employe = $this->em->getRepository(User::class)->findBy(['type' => 'EMPLOYE']);
                $data = $this->renderView('admin/newsletter/employe.html.twig', [
                    "employes" => $employe
                ]);
                $this->successResponse("Liste des newsletterclient ", $link, $data);

            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), $link);
            }

            return $this->json($this->result);
        }


            /**
            * @Route("/mailclient", name="mail-client")
            */
            public function sendMail(MailerInterface $mailer, Request $request): Response
            {
                
                $user = $this->getUser();
                $radio = $request->get("radio");
				$cb_client = $request->get("cb_client");
                //dd($cb_client);
				$destinateurs = $request->get("groupes");
                //dd($cb_client);
                if(!empty($destinateurs))
                {
                    $objet = $request->get("objet");
                    $message = $request->get("message");
                    
                        if($radio=="sms")
                        {
                            foreach($destinateurs as $destinateur)
                            {
                                $scinder = explode(",",$destinateur);
                                $numero = $scinder[0];
                                $sendername = $user->getNom();
                               
                                    $sms = new Sms();
                                    $sendsms= $sms->init("Notification","JOB");
                                   
                                    //$sendsms= $sms->sendSms($sms,$receiver,$sender);
                                    $sendsms= $sms->sendSms($message,$numero,$sendername);
                                   // dd($sendsms);

                                  // $sms = new Sms($numero, $message);
                                   //$sentMessage = $texter->send($sms);
                                    
                            } 
                            $this ->addFlash('success', "SMS envoyés avec succes");  
                            if(empty($cb_client)){
								return $this->redirectToRoute('employe-newsletter',[]);
							}else{
								return $this->redirectToRoute('client-newsletter',[]);
							}            
                            
                        }else
                        { 
                            foreach($destinateurs as $destinateur)
                            {
                                $scinder = explode(",",$destinateur);
                                $mail = $scinder[1];
                                

                                $email = (new Email())
                                ->from('KTC@GMAIL.com')
                                ->to( $mail)
                                //->cc('cc@example.com')
                                //->bcc('bcc@example.com')
                                //->replyTo('fabien@example.com')
                                //->priority(Email::PRIORITY_HIGH)
                                ->subject($objet)
                                ->text($message)
                                //->html('<p>See Twig integration for better HTML integration!</p>')
                                ;

                                $mailer->send($email);
                                

                            }
                            $this ->addFlash( 'success', 'Email envoyés avec succes' );
							if(empty($cb_client)){
								return $this->redirectToRoute('employe-newsletter',[]);
							}else{
								return $this->redirectToRoute('client-newsletter',[]);
							}
                            
                        }
                }
				$this ->addFlash( 'fail', 'Selectionner un destinateur.' );
                if(empty($cb_client)){
					return $this->redirectToRoute('employe-newsletter',[]);
				}else{
					return $this->redirectToRoute('client-newsletter',[]);
				}

            }
        
            /**
            * @Route("/mailemploye", name="mail-employe")
            */
            public function sendMailEmploye(MailerInterface $mailer, Request $request): Response
            {
                
                $user = $this->getUser();
                $radio = $request->get("radio");
                //dd($radio);
                if(!empty($radio))
                {
                    $objet = $request->get("objet");
                    $message = $request->get("message");
                    $destinateurs = $request->get("groupes");
                    //dd($destinateurs);
                    
                        if($radio=="sms")
                        {
                            foreach($destinateurs as $destinateur)
                            {
                                $scinder = explode(",",$destinateur);
                                $numero = $scinder[0];
                                $sendername = $user->getNom();
                                    $sms = new Sms();
                                    $sendsms= $sms->init("title","subject");
                                    //$sendsms= $sms->sendSms($sms,$receiver,$sender);
                                    $sendsms= $sms->sendSms($message,$numero,$sendername);
                                    //dd($sendsms);
                            } 
                            $this ->addFlash('sms', "SMS envoyés avec succes"
                            );              
                            
                        }else
                        { 
                            foreach($destinateurs as $destinateur)
                            {
                                $scinder = explode(",",$destinateur);
                                $mail = $scinder[1];
                                

                                $email = (new Email())
                                ->from('KTC@GMAIL.com')
                                ->to( $mail)
                                //->cc('cc@example.com')
                                //->bcc('bcc@example.com')
                                //->replyTo('fabien@example.com')
                                //->priority(Email::PRIORITY_HIGH)
                                ->subject($objet)
                                ->text($message)
                                //->html('<p>See Twig integration for better HTML integration!</p>')
                                ;

                                $mailer->send($email);

                            }
                            $this ->addFlash( 'success', 'Email envoyés avec succes' );
                            return $this->redirectToRoute('employe-newsletter',[]);


                        }
                }

            
       
            }


            /**
             * @Route("contactesite", name="contacte-site")
             */
            public function contactesite(Request $request)
            {
              // dd("fine");
              $name = $request->get("name");
              $phone = $request->get("phone");
              $email = $request->get("email");

              $externe = new ContactExterneSite();
              $externe->setName($name);
              $externe->setNumero($phone);
              $externe->setEmail($email);

              $this->em->persist($externe);
              $this->em->flush();
              $this ->addFlash( 'success', 'Salut ' .$name. ' Ton Newsletter a été envoyés avec succès' );
              return $this->redirectToRoute('indexfront',[]);


            }

            /**
             * @Route("ViewContact", name="ViewContact-Site" , methods={"GET"})
             */
            public function ViewContact()
            { 
                $link="ViewContact";
                try {
                    $viewexternes =$this->em->getRepository(ContactExterneSite::class)->findAll();
                    $data = $this->renderView('admin/newsletter/ViewExterne.html.twig', [
                       "viewexternes" => $viewexternes
                    ]);   
                    $this->successResponse("Liste des vue eexterne ", $link, $data);

                } catch (\Exception $ex) {
                    $this->log($ex->getMessage(), $link);
                }
                return $this->json($this->result);  
            }



            /**
            * @Route("/mailexterne", name="mail-externe")
            */
            public function mailexterne(MailerInterface $mailer, Request $request): Response
            {
                
                $user = $this->getUser();
                $radio = $request->get("radio");
                //dd($radio);
                if(!empty($radio))
                {
                    $objet = $request->get("objet");
                    $message = $request->get("message");
                    $destinateurs = $request->get("groupes");
                    //dd($destinateurs);
                    
                        if($radio=="sms")
                        {
                            foreach($destinateurs as $destinateur)
                            {
                                $scinder = explode(",",$destinateur);
                                $numero = $scinder[0];
                                $sendername = $user->getNom();
                                    $sms = new Sms();
                                    $sendsms= $sms->init("title","subject");
                                    //$sendsms= $sms->sendSms($sms,$receiver,$sender);
                                    $sendsms= $sms->sendSms($message,$numero,$sendername);
                                    //dd($sendsms);
                            } 
                            $this ->addFlash('sms', "SMS envoyés avec succes"
                            );              
                            
                        }else
                        { 
                            foreach($destinateurs as $destinateur)
                            {
                                $scinder = explode(",",$destinateur);
                                $mail = $scinder[1];
                                

                                $email = (new Email())
                                ->from('KTC@GMAIL.com')
                                ->to( $mail)
                                ->subject($objet)
                                ->text($message);

                                $mailer->send($email);

                            }
                            $this ->addFlash( 'success', 'Email envoyés avec succes a  ' );
                            return $this->redirectToRoute('ViewContact-Site',[]);


                        
                        }

                }
            }





    
}
