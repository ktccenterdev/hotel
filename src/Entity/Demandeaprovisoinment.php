<?php

namespace App\Entity;

use App\Repository\DemandeaprovisoinmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DemandeaprovisoinmentRepository::class)
 */
class Demandeaprovisoinment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Magasin::class, inversedBy="demandeaprovisoinments")
     */
    private $magasin;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="demandeaprovisoinments")
     */
    private $responsabledemande;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $etat;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentaire;

    /**
     * @ORM\OneToMany(targetEntity=Demandeitem::class, mappedBy="demande")
     */
    private $demandeitems;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datedemande;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createat;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedate;

    public function __construct()
    {
        $this->demandeitems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getResponsabledemande(): ?User
    {
        return $this->responsabledemande;
    }

    public function setResponsabledemande(?User $responsabledemande): self
    {
        $this->responsabledemande = $responsabledemande;

        return $this;
    }

    public function getEtat(): ?int
    {
        return $this->etat;
    }

    public function setEtat(?int $etat): self
    {
        $this->etat = $etat;

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
     * @return Collection|Demandeitem[]
     */
    public function getDemandeitems(): Collection
    {
        return $this->demandeitems;
    }

    public function addDemandeitem(Demandeitem $demandeitem): self
    {
        if (!$this->demandeitems->contains($demandeitem)) {
            $this->demandeitems[] = $demandeitem;
            $demandeitem->setDemande($this);
        }

        return $this;
    }

    public function removeDemandeitem(Demandeitem $demandeitem): self
    {
        if ($this->demandeitems->removeElement($demandeitem)) {
            // set the owning side to null (unless already changed)
            if ($demandeitem->getDemande() === $this) {
                $demandeitem->setDemande(null);
            }
        }

        return $this;
    }

    public function getDatedemande(): ?\DateTimeInterface
    {
        return $this->datedemande;
    }

    public function setDatedemande(?\DateTimeInterface $datedemande): self
    {
        $this->datedemande = $datedemande;

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

    public function getUpdatedate(): ?\DateTimeInterface
    {
        return $this->updatedate;
    }

    public function setUpdatedate(?\DateTimeInterface $updatedate): self
    {
        $this->updatedate = $updatedate;

        return $this;
    }
}
