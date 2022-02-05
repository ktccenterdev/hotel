<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProduitRepository::class)
 */
class Produit
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="text" , nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text")
     */
    private $photo;

    /**
     * @ORM\Column(type="integer")
     */
    private $qtseuil;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=Sortiritem::class, mappedBy="produit")
     */
    private $sortiritems;

    /**
     * @ORM\OneToMany(targetEntity=Entreitem::class, mappedBy="produit")
     */
    private $entreitems;

    public function __construct()
    {
        $this->sortiritems = new ArrayCollection();
        $this->entreitems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getQtseuil(): ?int
    {
        return $this->qtseuil;
    }

    public function setQtseuil(int $qtseuil): self
    {
        $this->qtseuil = $qtseuil;

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

    /**
     * @return Collection|Sortiritem[]
     */
    public function getSortiritems(): Collection
    {
        return $this->sortiritems;
    }

    public function addSortiritem(Sortiritem $sortiritem): self
    {
        if (!$this->sortiritems->contains($sortiritem)) {
            $this->sortiritems[] = $sortiritem;
            $sortiritem->setProduit($this);
        }

        return $this;
    }

    public function removeSortiritem(Sortiritem $sortiritem): self
    {
        if ($this->sortiritems->removeElement($sortiritem)) {
            // set the owning side to null (unless already changed)
            if ($sortiritem->getProduit() === $this) {
                $sortiritem->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Entreitem[]
     */
    public function getEntreitems(): Collection
    {
        return $this->entreitems;
    }

    public function addEntreitem(Entreitem $entreitem): self
    {
        if (!$this->entreitems->contains($entreitem)) {
            $this->entreitems[] = $entreitem;
            $entreitem->setProduit($this);
        }

        return $this;
    }

    public function removeEntreitem(Entreitem $entreitem): self
    {
        if ($this->entreitems->removeElement($entreitem)) {
            // set the owning side to null (unless already changed)
            if ($entreitem->getProduit() === $this) {
                $entreitem->setProduit(null);
            }
        }

        return $this;
    }
}
