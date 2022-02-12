<?php

namespace App\Controller;

use \Datetime;
use Exception;
use App\Entity\Log;
use App\Entity\Role;
use App\Entity\Action;
use App\Entity\Entene;
use App\Entity\Module;
use App\Entity\Parametre;
use App\Entity\ActionRole;
use App\Exception\ErrorException;
use App\Controller\RecuController;
use App\Repository\ActionRepository;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DefaultController extends AbstractController
{
    

    public $em;
    public $passwordEncoder;
    public $session;
    protected $requestStack;
    protected TCPDFController $tcpdf;
    protected $parametre;
    private ?string $reservations = NULL;
    protected $routes = array('search-client');
    protected $verbes = ['GET' => "Afficher", "POST" => "Insérer", "PUT"=>"Modifier", "DELETE"=>"Supprimer", "HEAD"=>"Demande", "CONNECT"=>"Connecter", "OPTIONS" => "Décrire", "TRACE" => "Reéaliser", "PATCH" => "Appliquer les modifications"];

    public function __construct(TCPDFController $tcpdf,RequestStack $requestStack,EntityManagerInterface $em,UserPasswordEncoderInterface $passwordEncoder)
    {
       
        $this->em = $em;
        $this->tcpdf = $tcpdf;
        $this->passwordEncoder = $passwordEncoder;
        $this->session= new Session();
        $this->requestStack = $requestStack;
        $request = $this->requestStack->getCurrentRequest();
        $this->parametre = current($this->em->getRepository(Parametre::class)->findAll()); 

        $this->checksecurity();
    }
    
    
    
    public function successResponse($msg, $link=NULL, $data=[])
    {
       
        return $this->result = $data ? $data : ['link'=>$link, 'parameters'=>[]];
    }

    public function log($msg=null, $link=null, $type=null,$data=[] ){
        return $this->result = throw new ErrorException($msg);
    }



    public function failResponse($msg, $link=NULL, $data=[])
    {
        return  $this->result = array(
            "failed" => true,
            "link" => $link,
            "message"=>$msg."En Echec !",
            "data" => $data
        );
    }
    public function checklogin(){

        $session = $this->requestStack->getCurrentRequest()->getSession();
        if (null !== $session && ! empty($this->getUser()) ) {
            return true;
        } else {
            return false;
        }
       
    }


    function template_error($path){
        ob_start( ) ;
        include $path ;
        return ob_get_clean( ) ;
    }

    public function checksecurity(){
        // dd($this->getUser());
        if (null === $this->session) {
            $this->redirect("/login");
        }else{
            $attributes = $this->requestStack->getCurrentRequest()->attributes;
            $route = $attributes->get('_route');   
            if(!in_array($route, $this->routes)){
                $droits=$this->session->get('userdroit'); 
                if ($droits && !in_array($route, $droits) && $route != "dashboard"){
                    $html = $this->template_error("403.html");
                    die($html);          
                }
            }
            
        }
        
    }

    private function addAction($request){
        $attributes = $request->attributes;
        $route = $attributes->get('_route');   
        $method = $request->getMethod();
        $item = explode("::", explode("\\", $attributes->get('_controller'))[2]);
        $moduleName = strtoupper(current(explode("Controller", current($item))));
        $actionName = $this->verbes[$method]." => ".end($item);
       if($moduleName != "VUEFRONT"){
           $module = $this->em->getRepository(Module::class)->findOneBy(['nom'=>$moduleName]);
           if(is_null($module)){
            $module = new Module();
            $module->setNom($moduleName);
            $this->em->persist($module);
           }
           $action = $this->em->getRepository(Action::class)->findOneBy(['cle'=>$route]);
           $etat = $method === "GET" ? 1 : 0;
           if(is_null($action)){
                $action = new Action();
                $action->setCle($route);
                $action->setModule($module);
                $action->setNom($actionName);
                $action->setIsdefault($etat);
                $this->em->persist($action);
           }
           $roles=$this->em->getRepository(Role::class)->findAll();                
            foreach ($roles as $role) {
                $actionrole= new ActionRole();
                $actionrole->setRole($role);
                $actionrole->setAction($action);
                $actionrole->setEtat($etat);
                $this->em->persist($actionrole);
            } 
           $this->em->flush();
       }
    }
    


    public function returnPDFLandscapeResponseFromHTML($html, $filename)
    {
        $pdf = $this->tcpdf->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true);
        $pdf->SetAuthor('church');
        $pdf->SetTitle(($filename));
        $pdf->SetSubject('church');
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 10, '', true);
        $pdf->SetMargins(3, 2, 2,  false);
        $pdf->setHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));
        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
        // set header and footer fonts
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        // set margins
        $pdf->SetMargins(6, 10, 6);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);

        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->AddPage('L', 'A4');

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        ob_end_clean();
        $pdf->Output($filename . ".pdf", 'I'); // This will output the PDF as a response directly
    }
    
    /**
     * Impression portrait
     */
    public function returnPDFResponseFromHTML($html, $filename, $orientation="P")
    {
        //private ?string $reservations = NULL;
        // $pdfObj = $this->get("white_october.tcpdf")->create();
        $pdf = $this->tcpdf->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor('church');
        $pdf->SetTitle($filename);
        $pdf->SetSubject('church');
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 10, '', true);
        $pdf->SetMargins(3, 2, 2,  false);
        $pdf->setHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));
        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
        // set header and footer fonts
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        // set margins
        $pdf->SetMargins(6, 10, 6);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);

        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->AddPage($orientation, 'A4');

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        ob_end_clean();
        $pdf->Output($filename . ".pdf", 'I'); // This will output the PDF as a response directly

    }
    
    public function printRecu($allocation){
           
        $dimension = array(110, 55);

        $pdf = new RecuController('P', 'mm', $dimension);

        $pdf->setAllocationinfo($allocation);
        
        // $pdf->setContribution($entree);

        $pdf->AliasNbPages();
        $pdf->SetFont('Courier', '', 10);
        $pdf->AddPage();
        
        return new Response(
            $pdf->Output(),
            200,
            array('Content-Type' => 'application/pdf')
        );
    }


    public function setlog($action,$description,$type,$idobject=null){
        $vurrenttime = new DateTime();
        $log =new Log();
        $log->setCreateat($vurrenttime);
        $log->setAction($action);
        $log->setDescription($description);
        $log->setType($type);
        $log->setIdobject($idobject);
        $log->setOperateur($this->getUser());
        $this->em->persist($log);
        $this->em->flush();
    }



    public function returnPDFrecu($html, $filename)
    {
        $pdf = $this->tcpdf->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true);
        $pdf->SetAuthor('church');
        $pdf->SetTitle(($filename));
        $pdf->SetSubject('church');
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 10, '', true);
        $pdf->SetMargins(3, 2, 2,  false);
        $pdf->setHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));
        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
        // set header and footer fonts
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        // set margins
        $pdf->SetMargins(6, 10, 6);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);

        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->AddPage('L', 'A4');

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        ob_end_clean();
        $pdf->Output($filename . ".pdf", 'I'); // This will output the PDF as a response directly
    }

    public function sommeMontant($array){
      return  array_sum(array_map(function($item) { 
            return $item->getMontant(); 
            }, $array));
    }

    public function genererCode($prefixe){
        $aleatoire = substr(number_format(time() * Rand(),0,'',''),0,4);
        $code = date("Y").date("m").date("d").$aleatoire;
        return $prefixe.$code;
    }

    public function getAllAntennes(){
        return $this->em->getRepository(Entene::class)->findAll();
    }


}
