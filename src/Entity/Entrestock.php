<?php

namespace App\Entity;

use App\Repository\EntrestockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EntrestockRepository::class)
 */
class Entrestock
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="entrestocks")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Magasin::class, inversedBy="entrestocks")
     */
    private $magasin;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentaire;

    /**
     * @ORM\OneToMany(targetEntity=Entreitem::class, mappedBy="entre")
     */
    private $entreitems;

    /**
     * @ORM\ManyToOne(targetEntity=Fournisseur::class, inversedBy="entrestocks")
     */
    private $fournisseur;

    /**
     * @ORM\Column(type="integer")
     */
    private $etat;

    public function __construct()
    {
        $this->entreitems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMagasin(): ?Magasin
    {
        return $this->magasin;
    }

    public function setMagasin(?Magasin $magasin): self
    {
        $this->magasin = $magasin;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;

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
            $entreitem->setEntre($this);
        }

        return $this;
    }

    public function removeEntreitem(Entreitem $entreitem): self
    {
        if ($this->entreitems->removeElement($entreitem)) {
            // set the owning side to null (unless already changed)
            if ($entreitem->getEntre() === $this) {
                $entreitem->setEntre(null);
            }
        }

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

    public function getEtat(): ?int
    {
        return $this->etat;
    }

    public function setEtat(int $etat): self
    {
        $this->etat = $etat;

        return $this;
    }
}
