<?php

namespace App\Entity;

use App\Repository\EntreitemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EntreitemRepository::class)
 */
class Entreitem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Entrestock::class, inversedBy="entreitems")
     */
    private $entre;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $qt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $pu;

    /**
     * @ORM\Column(type="float")
     */
    private $pixtotal;

    /**
     * @ORM\ManyToOne(targetEntity=Produit::class, inversedBy="entreitems")
     */
    private $produit;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntre(): ?Entrestock
    {
        return $this->entre;
    }

    public function setEntre(?Entrestock $entre): self
    {
        $this->entre = $entre;

        return $this;
    }

    public function getQt(): ?float
    {
        return $this->qt;
    }

    public function setQt(?float $qt): self
    {
        $this->qt = $qt;

        return $this;
    }

    public function getPu(): ?float
    {
        return $this->pu;
    }

    public function setPu(?float $pu): self
    {
        $this->pu = $pu;

        return $this;
    }

    public function getPixtotal(): ?float
    {
        return $this->pixtotal;
    }

    public function setPixtotal(float $pixtotal): self
    {
        $this->pixtotal = $pixtotal;

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
}
