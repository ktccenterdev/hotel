<?php

namespace App\Entity;

use App\Repository\SortirstockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SortirstockRepository::class)
 */
class Sortirstock
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Magasin::class, inversedBy="sortirstocks")
     */
    private $magdepart;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="sortirstocks")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="sortirstocks")
     */
    private $responsable;

    /**
     * @ORM\ManyToOne(targetEntity=Magasin::class, inversedBy="sortirstocks")
     */
    private $magdest;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commantaire;

    /**
     * @ORM\OneToMany(targetEntity=Sortiritem::class, mappedBy="sortistock")
     */
    private $sortiritems;

   

    public function __construct()
    {
        $this->sortiritems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMagdepart(): ?Magasin
    {
        return $this->magdepart;
    }

    public function setMagdepart(?Magasin $magdepart): self
    {
        $this->magdepart = $magdepart;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getResponsable(): ?User
    {
        return $this->responsable;
    }

    public function setResponsable(?User $responsable): self
    {
        $this->responsable = $responsable;

        return $this;
    }

    public function getMagdest(): ?Magasin
    {
        return $this->magdest;
    }

    public function setMagdest(?Magasin $magdest): self
    {
        $this->magdest = $magdest;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCommantaire(): ?string
    {
        return $this->commantaire;
    }

    public function setCommantaire(?string $commantaire): self
    {
        $this->commantaire = $commantaire;

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
            $sortiritem->setSortistock($this);
        }

        return $this;
    }

    public function removeSortiritem(Sortiritem $sortiritem): self
    {
        if ($this->sortiritems->removeElement($sortiritem)) {
            // set the owning side to null (unless already changed)
            if ($sortiritem->getSortistock() === $this) {
                $sortiritem->setSortistock(null);
            }
        }

        return $this;
    }

    

    

   
}
