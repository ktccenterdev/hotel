<?php

namespace App\Entity;

use App\Repository\SortiritemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SortiritemRepository::class)
 */
class Sortiritem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Sortirstock::class, inversedBy="sortiritems")
     */
    private $sortistock;

    /**
     * @ORM\ManyToOne(targetEntity=Produit::class, inversedBy="sortiritems")
     */
    private $produit;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $qt;

   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSortistock(): ?Sortirstock
    {
        return $this->sortistock;
    }

    public function setSortistock(?Sortirstock $sortistock): self
    {
        $this->sortistock = $sortistock;

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

    public function getQt(): ?float
    {
        return $this->qt;
    }

    public function setQt(?float $qt): self
    {
        $this->qt = $qt;

        return $this;
    }

    
}
