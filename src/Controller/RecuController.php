<?php

    namespace App\Controller;



class RecuController  extends \FPDF
    {

        private $allocationinfo;

        public function setAllocationinfo($allocationinfo){
            $this->allocationinfo = $allocationinfo;
        }

        function Header()
        {
            $dateImpression = date("d-m-Y");
            $heureImpression = date("H:i:s");
            $logo = "img/logo.jpeg"; 
            $this->Image($logo,2,9,10);
            $this->SetFont('Times','B',8);  
            $this->Cell(0,-10,utf8_decode($this->allocationinfo->getAntene()->getNom()),0,0,'C');
            $this->SetFont('Times',"" ,5);
            $this->Cell(-37,-3,utf8_decode($this->allocationinfo->getAntene()->getLocalisation()),0,0,'C');
            $this->SetFont('Arial',"",6);
            $this->Cell(44,6,"BP: ".utf8_decode($this->allocationinfo->getAntene()->getBp()).", ".utf8_decode("YAOUNDE").utf8_decode(",  Tél: ".$this->allocationinfo->getAntene()->getTel()),0,0,'C');
            $this->Cell(-53,11,"Email: ".utf8_decode($this->allocationinfo->getAntene()->getEmail()),0,0,'C');
            $this->Cell(47.5,16,utf8_decode("Web: ".$this->allocationinfo->getAntene()->getSite()),0,0,'C');
            $this->Line(3,20,50,20);
            $this->Ln(8);
            $y = $this->getY();
            $this->SetFont('Times',"BU", 7);
            $dateParticipation = date_format(new \DateTime("now"), 'd-m-Y');
            $this->Cell(0,20,utf8_decode("FACTURE n° ".$this->allocationinfo->getId()),0,0,'C');
            $this->Ln(6);
            $this->SetFont('Courier',"B", 10);
            $this->setY(32);

            // $this->setX(5);
            // $this->Cell(45,5,utf8_decode(strtoupper("NUMERO:".$this->allocationinfo->getId())),1,0,'C');
            // $this->Ln(5);
            $this->SetFont('Courier',"B", 7);
            $this->setX(5); 
            $this->MultiCell(45, 5, utf8_decode($this->allocationinfo->getOccupant()->getNom()." ".$this->allocationinfo->getOccupant()->getPrenom()), 0, 'C', 0);
            $this->Ln(1);
            $this->SetFont('Courier',"B", 5);
            $this->Cell(0, 0,utf8_decode(" ".$dateImpression."-".$heureImpression), 0, 0, 'C');
            $this->Ln(5);
            $total = 0;
            $this->setX(5);
            $this->SetFont('Courier', 'B', 8);
            $this->MultiCell(45, 5, utf8_decode("CHAMBRE: ".$this->allocationinfo->getChambre()->getNumero()), 1, 'C', 0);
            $this->ln(2);
            $this->setX(30);
            $this->SetFont('Courier', '', 7);
            $this->setX(5);
            $this->Cell(15, 5, utf8_decode('Montant:'), 0, 0, 'L');
            $this->setX(20);
            $this->SetFont('Courier', 'B', 8);
            $this->Cell(20, 5, utf8_decode($this->allocationinfo->getMontant()." FCFA"), 0, 0, 'C');

            
            $this->Ln(5);
            $this->setX(20);
            $this->SetFont('Courier', '', 7);
            $this->setX(5);
            $this->Cell(15, 5, utf8_decode('Arrivée:'), 0, 0, 'L');
            $this->setX(25);
            $this->SetFont('Courier', 'B', 8);
            $this->Cell(20, 5,$this->allocationinfo->getDatedebut()->format('Y/m/d H:i:s'), 0, 0, 'C');
            $this->Ln(5);

            $this->setX(30);
            $this->SetFont('Courier', '', 7);
            $this->setX(5);
            $this->Cell(15, 5, utf8_decode('Départ: '), 0, 0, 'L');
            $this->setX(25);
            $this->SetFont('Courier', 'B', 8);
            $this->Cell(20, 5, $this->allocationinfo->getDatefin()->format('Y/m/d H:i:s'), 0, 0, 'C');
            $this->Ln(5);

            $this->setX(30);
            $this->SetFont('Courier', '', 7);
            $this->setX(5);
            $this->Cell(15, 5, utf8_decode('Type: '), 0, 0, 'L');
            $this->setX(25);
            $this->SetFont('Courier', 'B', 8);
            $type = $this->allocationinfo->getType();
            
            $this->Cell(20, 5, utf8_decode($type." (".$this->allocationinfo->getDuree().")"), 0, 0, 'L');
            $this->ln(5);
            
            $this->setX(30);            
            $this->Ln(1);
            $this->SetFont('Times', 'UI', 6);
            $this->Cell(0, $y, utf8_decode('Opérateur'), 0, 0, 'C');
            $this->Ln(3);
            $this->SetFont('Times', 'B', 6);
            $this->Cell(0, $y, utf8_decode($this->allocationinfo->getOperateur()->getUsername()), 0, 0, 'C');
            
            $this->SetFont('courier', '', 8);
            //$this->Cell(45, $y-10, utf8_decode($dateImpression. ' à '.$heureImpression), 0, 0, 'C');
            $this->Ln(2);
            $this->SetFont('courier', 'I', 7);
            $this->setX(0);
            $this->Cell(0, $y, utf8_decode('By KTC-Center, www.kamer-center.net'), 0,0,1);
            //end contribution

        }
        // function Header()
        // {
            

        //     $logo = "img/eec.jpeg"; 
        //     $this->Image($logo,2,9,10);

        //     // Police Courier gras 15
        //     $this->SetFont('Times','B',6);
        //     // Décalage à droite

        //     $this->Cell(0,-10,utf8_decode("EGLISE EVANGELIQUE DU CAMEROUN"),0,0,'C');
        //     // Saut de ligne
        //     //$this->Ln(5);
        //     $this->SetFont('Times',"B" ,6);
        //     //$this->Cell(83);
        //     $this->Cell(-37,-3,utf8_decode($parametre['nom']),0,0,'C');
        //     //la boite postale, ville et telephone
        //     $this->SetFont('Arial',"",5);
        //     $this->Cell(44,6,"BP: ".utf8_decode($parametre['bp']).", ".utf8_decode($parametre['siege']).utf8_decode(",  Tél: ".$parametre['telephone']),0,0,'C');

        //     //Adresse email
        //     $this->Cell(-53,11,"Email: ".utf8_decode($parametre['email']),0,0,'C');

        //     //Site web
        //     $this->Cell(47.5,16,utf8_decode($parametre['siteweb']),0,0,'C');
        //     //trait de soulignement
        //     $this->Line(3,20,50,20);

        //     //saut de ligne
        //     $this->Ln(8);
        //     $y = $this->getY();
        //     //mise en forme: police Courier, Gras et souligné
        //     $this->SetFont('Times',"BU", 7);

        //     //ticket de preparation
        //     $dateParticipation = date_format(new \DateTime($contribution['createdAt']), 'd-m-Y');
        //     $this->Cell(0,20,utf8_decode("RECU DE VERSEMENT"),0,0,'C');
        //     //saut de ligne
        //     $this->Ln(6);
        //     //mise en forme
        //     $this->SetFont('Courier',"B", 10);
        //     //le code du fidèle
        //     $this->setY(32);
        //     if($contributeur['personne'] != null){    
        //         $this->setX(5);
        //         $this->Cell(45,5,utf8_decode(strtoupper($contributeur['personne']['code'])),1,0,'C');
        //         //saut de ligne
        //         $this->Ln(5);
        //         $this->SetFont('Courier',"B", 7); 
        //         $this->setX(5);            
        //         $this->MultiCell(45, 5, utf8_decode($contributeur['personne']['nom'].' '.$contributeur['personne']['prenom']), 0, 'C', 0);
        //         $this->Ln(1);
        //         $this->Cell(0, 0, "(".utf8_decode($contributeur['personne']['status']).")", 0, 0, 'C');
        //     }else if($contributeur['groupe'] != null){
        //         $this->setX(5);
        //         $this->Cell(45,5, "GROUPE",1,0,'C');
        //         //saut de ligne
        //         $this->Ln(5);
        //         $this->SetFont('Courier',"B", 9);
        //         //le nom
        //         $this->setX(5);
        //         $this->MultiCell(45, 5, utf8_decode($contributeur['groupe']['nom']), 0, 'C', 0);
        //     }else if($contributeur['mariage'] != null){
        //         $this->setX(5);
        //         $this->Cell(45,5, "MARIAGE",1,0,'C');
        //         //saut de ligne
        //         $this->Ln(5);
        //         $this->SetFont('Courier',"B", 9);
        //         //le nom
        //         $this->setX(5);
        //         $this->MultiCell(45, 5, utf8_decode($contributeur['mariage']['intitule']), 0, 'C', 0);
        //     }else if($contributeur['autre'] != "autre"){
        //         $this->Cell(0,5, "PARTENAIRES",1,0,'C');

        //         //saut de ligne
        //         $this->Ln(5);
        //         $this->SetFont('Courier',"B", 8);
        //         //le nom
        //         $this->MultiCell(0, 5, utf8_decode($contributeur['autre']), 0, 'C', 0);
        //     }
            
        //     //saut de ligne
        //     $this->Ln(5);
        //     $total = 0;
        //     //  foreach ($contributions as $key => $value) {
        //         $this->setX(5);
        //         $this->SetFont('Courier', 'B', 8);
        //         $this->MultiCell(45, 5, utf8_decode($entreeFinanciere['intitule']), 1, 'C', 0);
        //         $this->setX(30);
        //         $total = $contribution['montant'];
        //     //    }

        //     //les totaux
        //     $this->SetFont('Courier', 'B', 8.5);
        //     $this->setX(5);
        //     $this->Cell(25, 5, utf8_decode('MONTANT(FCFA)'), 1, 0, 'L');
        //     $this->setX(30);
        //     $this->SetFont('Courier', 'B', 10);
        //     $this->Cell(20, 5, utf8_decode($total), 1, 0, 'C');

        //     //opérateur
        //     $this->Ln(1);
        //     $this->SetFont('Times', 'UI', 6);
        //     $this->Cell(0, $y, utf8_decode('Opérateur'), 0, 0, 'C');
        //     //nom de l'opérateur
        //     $this->Ln(3);
        //     $this->SetFont('Times', 'B', 6);
        //     $this->Cell(0, $y, utf8_decode($operateur['personne']['nom']." ".$operateur['personne']['prenom']), 0, 0, 'C');
        //     //le code bare
        //     $this->Ln(15);
        //     $y2 = $this->getY()-1;
        //     $options = array(
        //         'code'   => $operateur['personne']['code'],
        //         'type'   => 'qrcode',
        //         'format' => 'png',
        //         'width'  => 10,
        //         'height' => 10,
        //         'color'  => array(10, 10, 10),
        //     );
        //     $savePath = 'img/qrcode/';
        //     $fileName = $operateur['personne']['code'].'.png';
        //     $generator = new Generator();
        //     $qrcode = $generator->generate($options);
        //     file_put_contents($savePath.$fileName, base64_decode($qrcode));
        //  //   $this->Image($savePath.$fileName,2, $y2,10, "png");

        //     //la date
        //     $dateImpression = date("d-m-Y");//date_format($contribution['createdAt'], 'd-m-Y');
        //     $heureImpression = date("H:i:s");//date_format($contribution['createdAt'], 'H:i:s');
        //     $this->SetFont('courier', '', 8);
        //     $this->Cell(45, $y-10, utf8_decode($dateImpression. ' à '.$heureImpression), 0, 0, 'C');
        //     //Auteur
        //     $this->Ln(2);
        //     $this->SetFont('courier', 'I', 7);
        //     $this->setX(0);
        //     $this->Cell(0, $y, utf8_decode('By KTC-Center, www.kamer-center.net'), 0,0,1);

        // }


    }
     

?>