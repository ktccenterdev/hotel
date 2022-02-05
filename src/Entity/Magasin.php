<?php

namespace App\Entity;

use App\Repository\MagasinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MagasinRepository::class)
 */
class Magasin
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
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\OneToOne(targetEntity=Entene::class, inversedBy="magasin", cascade={"persist", "remove"})
     */
    private $antene;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Entrestock::class, mappedBy="magasin")
     */
    private $entrestocks;

    /**
     * @ORM\OneToMany(targetEntity=Sortirstock::class, mappedBy="magdepart")
     */
    private $sortirstocks;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 0})
     */
    private $defaut;

    public function __construct()
    {
        $this->entrestocks = new ArrayCollection();
        $this->sortirstocks = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Entrestock[]
     */
    public function getEntrestocks(): Collection
    {
        return $this->entrestocks;
    }

    public function addEntrestock(Entrestock $entrestock): self
    {
        if (!$this->entrestocks->contains($entrestock)) {
            $this->entrestocks[] = $entrestock;
            $entrestock->setMagasin($this);
        }

        return $this;
    }

    public function removeEntrestock(Entrestock $entrestock): self
    {
        if ($this->entrestocks->removeElement($entrestock)) {
            // set the owning side to null (unless already changed)
            if ($entrestock->getMagasin() === $this) {
                $entrestock->setMagasin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Sortirstock[]
     */
    public function getSortirstocks(): Collection
    {
        return $this->sortirstocks;
    }

    public function addSortirstock(Sortirstock $sortirstock): self
    {
        if (!$this->sortirstocks->contains($sortirstock)) {
            $this->sortirstocks[] = $sortirstock;
            $sortirstock->setMagdepart($this);
        }

        return $this;
    }

    public function removeSortirstock(Sortirstock $sortirstock): self
    {
        if ($this->sortirstocks->removeElement($sortirstock)) {
            // set the owning side to null (unless already changed)
            if ($sortirstock->getMagdepart() === $this) {
                $sortirstock->setMagdepart(null);
            }
        }

        return $this;
    }

    public function getDefaut(): ?bool
    {
        return $this->defaut;
    }

    public function setDefaut(?bool $defaut): self
    {
        $this->defaut = $defaut;

        return $this;
    }
}
