<?php

namespace App\Entity;

use App\Repository\DemandeitemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DemandeitemRepository::class)
 */
class Demandeitem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Demandeaprovisoinment::class, inversedBy="demandeitems")
     */
    private $demande;

    /**
     * @ORM\ManyToOne(targetEntity=Produit::class, inversedBy="demandeitems")
     */
    private $produit;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $quantite;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createat;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateat;

    
    public function __construct()
    {
       
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDemande(): ?Demandeaprovisoinment
    {
        return $this->demande;
    }

    public function setDemande(?Demandeaprovisoinment $demande): self
    {
        $this->demande = $demande;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(?float $quantite): self
    {
        $this->quantite = $quantite;

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

}
