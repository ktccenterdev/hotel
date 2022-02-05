<?php

namespace App\Entity;

use App\Repository\ChambreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ChambreRepository::class)
 */
class Chambre
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
    private $numero;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $surface;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbenafant;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbaldulte;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nblit;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo4;

    /**
     * @ORM\ManyToOne(targetEntity=Typechambre::class, inversedBy="chambres")
     */
    private $type;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateAt;

    /**
     * @ORM\ManyToOne(targetEntity=Entene::class, inversedBy="chambres")
     * @ORM\JoinColumn(nullable=false)
     */
    private $entene;

    /**
     * @ORM\OneToMany(targetEntity=Allocation::class, mappedBy="chambre")
     */
    private $allocations;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="chambre")
     */
    private $reservations;

    public function __construct()
    {
        $this->allocations = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getSurface(): ?float
    {
        return $this->surface;
    }

    public function setSurface(?float $surface): self
    {
        $this->surface = $surface;

        return $this;
    }

    public function getNbenafant(): ?int
    {
        return $this->nbenafant;
    }

    public function setNbenafant(?int $nbenafant): self
    {
        $this->nbenafant = $nbenafant;

        return $this;
    }

    public function getNbaldulte(): ?int
    {
        return $this->nbaldulte;
    }

    public function setNbaldulte(?int $nbaldulte): self
    {
        $this->nbaldulte = $nbaldulte;

        return $this;
    }

    public function getNblit(): ?int
    {
        return $this->nblit;
    }

    public function setNblit(?int $nblit): self
    {
        $this->nblit = $nblit;

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

    public function getPhoto1(): ?string
    {
        return $this->photo1;
    }

    public function setPhoto1(?string $photo1): self
    {
        $this->photo1 = $photo1;

        return $this;
    }

    public function getPhoto2(): ?string
    {
        return $this->photo2;
    }

    public function setPhoto2(?string $photo2): self
    {
        $this->photo2 = $photo2;

        return $this;
    }

    public function getPhoto3(): ?string
    {
        return $this->photo3;
    }

    public function setPhoto3(?string $photo3): self
    {
        $this->photo3 = $photo3;

        return $this;
    }

    public function getPhoto4(): ?string
    {
        return $this->photo4;
    }

    public function setPhoto4(?string $photo4): self
    {
        $this->photo4 = $photo4;

        return $this;
    }

    public function getType(): ?Typechambre
    {
        return $this->type;
    }

    public function setType(?Typechambre $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(?\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeInterface $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getEntene(): ?Entene
    {
        return $this->entene;
    }

    public function setEntene(?Entene $entene): self
    {
        $this->entene = $entene;

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
            $allocation->setChambre($this);
        }

        return $this;
    }

    public function removeAllocation(Allocation $allocation): self
    {
        if ($this->allocations->removeElement($allocation)) {
            // set the owning side to null (unless already changed)
            if ($allocation->getChambre() === $this) {
                $allocation->setChambre(null);
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
            $reservation->setChambre($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getChambre() === $this) {
                $reservation->setChambre(null);
            }
        }

        return $this;
    }
}
