<?php

namespace App\Entity;

use App\Repository\BeneficiaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BeneficiaireRepository::class)
 */
class Beneficiaire
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
    private $type;

    /**
     * @ORM\OneToOne(targetEntity=Fournisseur::class, cascade={"persist", "remove"})
     */
    private $fournisseur;

    /**
     * @ORM\OneToOne(targetEntity=User::class, cascade={"persist", "remove"})
     */
    private $autre;

    /**
     * @ORM\OneToMany(targetEntity=SortieFinanciere::class, mappedBy="beneficiaire")
     */
    private $sortieFinancieres;

    public function __construct()
    {
        $this->sortieFinancieres = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFournisseur(): ?Fournisseur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Fournisseur $fournisseur): self
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    public function getAutre(): ?User
    {
        return $this->autre;
    }

    public function setAutre(?User $autre): self
    {
        $this->autre = $autre;

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
            $sortieFinanciere->setBeneficiaire($this);
        }

        return $this;
    }

    public function removeSortieFinanciere(SortieFinanciere $sortieFinanciere): self
    {
        if ($this->sortieFinancieres->removeElement($sortieFinanciere)) {
            // set the owning side to null (unless already changed)
            if ($sortieFinanciere->getBeneficiaire() === $this) {
                $sortieFinanciere->setBeneficiaire(null);
            }
        }

        return $this;
    }
}
