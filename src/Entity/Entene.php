<?php

namespace App\Entity;

use App\Repository\EnteneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EnteneRepository::class)
 */
class Entene
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $localisation;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bp;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $site;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="antene")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=Chambre::class, mappedBy="entene")
     */
    private $chambres;

    /**
     * @ORM\OneToMany(targetEntity=Allocation::class, mappedBy="antene")
     */
    private $allocations;

    /**
     * @ORM\OneToOne(targetEntity=Magasin::class, mappedBy="antene", cascade={"persist", "remove"})
     */
    private $magasin;

    /**
     * @ORM\OneToMany(targetEntity=SortieFinanciere::class, mappedBy="antenne")
     */
    private $sortieFinancieres;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="antene")
     */
    private $reservations;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $acronym;

    /**
     * @ORM\OneToMany(targetEntity=Typechambre::class, mappedBy="antene")
     */
    private $typechambres;

    /**
     * @ORM\OneToMany(targetEntity=Tarif::class, mappedBy="antenne")
     */
    private $tarifs;

    private $typeDeChambres;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 0})
     */
    private $isPrincipal;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->chambres = new ArrayCollection();
        $this->allocations = new ArrayCollection();
        $this->sortieFinancieres = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->typechambres = new ArrayCollection();
        $this->tarifs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): self
    {
        $this->localisation = $localisation;

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

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getBp(): ?string
    {
        return $this->bp;
    }

    public function setBp(?string $bp): self
    {
        $this->bp = $bp;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getSite(): ?string
    {
        return $this->site;
    }

    public function setSite(?string $site): self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setAntene($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getAntene() === $this) {
                $user->setAntene(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Chambre[]
     */
    public function getChambres(): Collection
    {
        return $this->chambres;
    }

    public function addChambre(Chambre $chambre): self
    {
        if (!$this->chambres->contains($chambre)) {
            $this->chambres[] = $chambre;
            $chambre->setEntene($this);
        }

        return $this;
    }

    public function removeChambre(Chambre $chambre): self
    {
        if ($this->chambres->removeElement($chambre)) {
            // set the owning side to null (unless already changed)
            if ($chambre->getEntene() === $this) {
                $chambre->setEntene(null);
            }
        }

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
            $allocation->setAntene($this);
        }

        return $this;
    }

    public function removeAllocation(Allocation $allocation): self
    {
        if ($this->allocations->removeElement($allocation)) {
            // set the owning side to null (unless already changed)
            if ($allocation->getAntene() === $this) {
                $allocation->setAntene(null);
            }
        }

        return $this;
    }

    public function getMagasin(): ?Magasin
    {
        return $this->magasin;
    }

    public function setMagasin(?Magasin $magasin): self
    {
        // unset the owning side of the relation if necessary
        if ($magasin === null && $this->magasin !== null) {
            $this->magasin->setAntene(null);
        }

        // set the owning side of the relation if necessary
        if ($magasin !== null && $magasin->getAntene() !== $this) {
            $magasin->setAntene($this);
        }

        $this->magasin = $magasin;

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
            $sortieFinanciere->setAntenne($this);
        }

        return $this;
    }

    public function removeSortieFinanciere(SortieFinanciere $sortieFinanciere): self
    {
        if ($this->sortieFinancieres->removeElement($sortieFinanciere)) {
            // set the owning side to null (unless already changed)
            if ($sortieFinanciere->getAntenne() === $this) {
                $sortieFinanciere->setAntenne(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Reservation[]
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setAntene($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getAntene() === $this) {
                $reservation->setAntene(null);
            }
        }

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

    public function getAcronym(): ?string
    {
        return $this->acronym;
    }

    public function setAcronym(string $acronym): self
    {
        $this->acronym = $acronym;

        return $this;
    }

    /**
     * @return Collection|Typechambre[]
     */
    public function getTypechambres(): Collection
    {
        return $this->typechambres;
    }

    public function addTypechambre(Typechambre $typechambre): self
    {
        if (!$this->typechambres->contains($typechambre)) {
            $this->typechambres[] = $typechambre;
            $typechambre->setAntene($this);
        }

        return $this;
    }

    public function removeTypechambre(Typechambre $typechambre): self
    {
        if ($this->typechambres->removeElement($typechambre)) {
            // set the owning side to null (unless already changed)
            if ($typechambre->getAntene() === $this) {
                $typechambre->setAntene(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tarif[]
     */
    public function getTarifs(): Collection
    {
        return $this->tarifs;
    }

    public function addTarif(Tarif $tarif): self
    {
        if (!$this->tarifs->contains($tarif)) {
            $this->tarifs[] = $tarif;
            $tarif->setAntenne($this);
        }

        return $this;
    }

    public function removeTarif(Tarif $tarif): self
    {
        if ($this->tarifs->removeElement($tarif)) {
            // set the owning side to null (unless already changed)
            if ($tarif->getAntenne() === $this) {
                $tarif->setAntenne(null);
            }
        }

        return $this;
    }

    public function getIsPrincipal(): ?bool
    {
        return $this->isPrincipal;
    }

    public function setIsPrincipal(?bool $isPrincipal): self
    {
        $this->isPrincipal = $isPrincipal;

        return $this;
    }

    /**
     * Get the value of typeDeChambres
     */ 
    public function getTypeDeChambres()
    {
        $types = array();
        foreach ($this->typechambres as $typechambre) {
            $types[] = ['id'=>$typechambre->getId(), 'nom'=>$typechambre->getNom()];
        }
        return $types;
    }
}
