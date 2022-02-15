<?php

namespace App\Entity;

use App\Repository\AllocationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AllocationRepository::class)
 */
class Allocation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datedebut;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datefin;

   

   

    /**
     * @ORM\Column(type="float")
     */
    private $montant;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $reduction;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="allocations")
     */
    private $occupant;

    /**
     * @ORM\ManyToOne(targetEntity=Chambre::class, inversedBy="allocations")
     */
    private $chambre;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $createat;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $updateat;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $operateur;

    /**
     * @ORM\ManyToOne(targetEntity=Entene::class, inversedBy="allocations")
     */
    private $antene;

    /**
     * @ORM\ManyToOne(targetEntity=Compte::class, inversedBy="allocations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compte;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $departavant;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $departreel;

    private $duree;

    private $isTimeExpired;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatedebut(): ?\DateTimeInterface
    {
        return $this->datedebut;
    }

    public function setDatedebut(\DateTimeInterface $datedebut): self
    {
        $this->datedebut = $datedebut;

        return $this;
    }

    public function getDatefin(): ?\DateTimeInterface
    {
        return $this->datefin;
    }

    public function setDatefin(\DateTimeInterface $datefin): self
    {
        $this->datefin = $datefin;

        return $this;
    }
    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getReduction(): ?float
    {
        return $this->reduction;
    }

    public function setReduction(?float $reduction): self
    {
        $this->reduction = $reduction;

        return $this;
    }

    public function getOccupant(): ?User
    {
        return $this->occupant;
    }

    public function setOccupant(?User $occupant): self
    {
        $this->occupant = $occupant;

        return $this;
    }

    public function getChambre(): ?Chambre
    {
        return $this->chambre;
    }

    public function setChambre(?Chambre $chambre): self
    {
        $this->chambre = $chambre;

        return $this;
    }

    public function getCreateat(): ?\DateTimeInterface
    {
        return $this->createat;
    }

    public function setCreateat(?\DateTimeInterface $createat): self
    {
        $this->createat = $createat;

        return $this;
    }

    public function getUpdateat(): ?\DateTimeInterface
    {
        return $this->updateat;
    }

    public function setUpdateat(?\DateTimeInterface $updateat): self
    {
        $this->updateat = $updateat;

        return $this;
    }

    public function getOperateur(): ?User
    {
        return $this->operateur;
    }

    public function setOperateur(?User $operateur): self
    {
        $this->operateur = $operateur;

        return $this;
    }

    public function getAntene(): ?Entene
    {
        return $this->antene;
    }

    public function setAntene(?Entene $antene): self
    {
        $this->antene = $antene;

        return $this;
    }

    public function getCompte(): ?Compte
    {
        return $this->compte;
    }

    public function setCompte(?Compte $compte): self
    {
        $this->compte = $compte;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDepartavant(): ?bool
    {
        return $this->departavant;
    }

    public function setDepartavant(bool $departavant): self
    {
        $this->departavant = $departavant;

        return $this;
    }

    public function getDepartreel(): ?\DateTimeInterface
    {
        return $this->departreel;
    }

    public function setDepartreel(?\DateTimeInterface $departreel): self
    {
        $this->departreel = $departreel;

        return $this;
    }

    /**
     * Get the value of duree
     */ 
    public function getDuree()
    {
        if ($this->type == "SIESTE") {
            $occurence = round(abs(strtotime($this->datedebut->format('Y/m/d H:i:s')) - strtotime($this->datefin->format('Y/m/d H:i:s')))/3600)."h";
        } else {
            $occurence = round(abs(strtotime($this->datedebut->format('Y/m/d H:i:s')) - strtotime($this->datefin->format('Y/m/d H:i:s')))/86400)."j";
        }
        return $occurence;
    }

    /**
     * Get the value of isTimeExpired
     */ 
    public function getIsTimeExpired()
    {
        $now = new \DateTime();
        if($this->datefin > $now){
            return true;
        }else{
            return false;
        }
    }
}
