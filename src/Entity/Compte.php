<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CompteRepository;

/**
 * @ORM\Entity(repositoryClass=CompteRepository::class)
 */
class Compte
{
    use DefaultAttributes;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @ORM\Column(type="integer")
     */
    private $niveau;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $parent;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $intitule;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=Allocation::class, mappedBy="compte")
     */
    private $allocations;

    /**
     * @ORM\OneToMany(targetEntity=SortieFinanciere::class, mappedBy="compte")
     */
    private $sortieFinancieres;

    public function __construct(){
        $this->createdAt = new \DateTime('now');
        $this->allocations = new ArrayCollection();
        $this->sortieFinancieres = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getNiveau(): ?int
    {
        return $this->niveau;
    }

    public function setNiveau(int $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getParent(): ?int
    {
        return $this->parent;
    }

    public function setParent(?int $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): self
    {
        $this->intitule = $intitule;

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
     * @return Collection|Allocation[]
     */
    public function getAllocations(): Collection
    {
        return $this->allocations;
    }

    public function addAllocation(Allocation $allocation): self
    {
        if (!$this->allocations->contains($allocation)) {
            $this->allocations[] = $allocation;
            $allocation->setCompte($this);
        }

        return $this;
    }

    public function removeAllocation(Allocation $allocation): self
    {
        if ($this->allocations->removeElement($allocation)) {
            // set the owning side to null (unless already changed)
            if ($allocation->getCompte() === $this) {
                $allocation->setCompte(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SortieFinanciere[]
     */
    public function getSortieFinancieres(): Collection
    {
        return $this->sortieFinancieres;
    }

    public function addSortieFinanciere(SortieFinanciere $sortieFinanciere): self
    {
        if (!$this->sortieFinancieres->contains($sortieFinanciere)) {
            $this->sortieFinancieres[] = $sortieFinanciere;
            $sortieFinanciere->setCompte($this);
        }

        return $this;
    }

    public function removeSortieFinanciere(SortieFinanciere $sortieFinanciere): self
    {
        if ($this->sortieFinancieres->removeElement($sortieFinanciere)) {
            // set the owning side to null (unless already changed)
            if ($sortieFinanciere->getCompte() === $this) {
                $sortieFinanciere->setCompte(null);
            }
        }

        return $this;
    }
}
