<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReservationRepository::class)
 */
class Reservation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $datedariver;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $datedepart;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $heuredariver;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $heuredepart;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $montan;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $reduction;

    /**
     * @ORM\ManyToOne(targetEntity=Chambre::class, inversedBy="reservations")
     */
    private $chambre;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reservations")
     */
    private $client;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createat;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateat;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reservationsaves")
     */
    private $createby;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titre;

    /**
     * @ORM\ManyToOne(targetEntity=Typechambre::class, inversedBy="reservation")
     */
    private $typechambre;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantite;

    /**
     * @ORM\ManyToOne(targetEntity=Entene::class, inversedBy="reservations")
     */
    private $antene;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatedariver(): ?\DateTimeInterface
    {
        return $this->datedariver;
    }

    public function setDatedariver(?\DateTimeInterface $datedariver): self
    {
        $this->datedariver = $datedariver;

        return $this;
    }

    public function getDatedepart(): ?\DateTimeInterface
    {
        return $this->datedepart;
    }

    public function setDatedepart(?\DateTimeInterface $datedepart): self
    {
        $this->datedepart = $datedepart;

        return $this;
    }

    public function getHeuredariver(): ?\DateTimeInterface
    {
        return $this->heuredariver;
    }

    public function setHeuredariver(?\DateTimeInterface $heuredariver): self
    {
        $this->heuredariver = $heuredariver;

        return $this;
    }

    public function getHeuredepart(): ?\DateTimeInterface
    {
        return $this->heuredepart;
    }

    public function setHeuredepart(?\DateTimeInterface $heuredepart): self
    {
        $this->heuredepart = $heuredepart;

        return $this;
    }

    public function getMontan(): ?float
    {
        return $this->montan;
    }

    public function setMontan(?float $montan): self
    {
        $this->montan = $montan;

        return $this;
    }

    public function getReduction(): ?float
    {
        return $this->reduction;
    }

    public function setReduction(?float $reduction): self
    {
        $this->reduction = $reduction;

        return $this;
    }

    public function getChambre(): ?Chambre
    {
        return $this->chambre;
    }

    public function setChambre(?Chambre $chambre): self
    {
        $this->chambre = $chambre;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

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

    public function getUpdateat(): ?\DateTimeInterface
    {
        return $this->updateat;
    }

    public function setUpdateat(?\DateTimeInterface $updateat): self
    {
        $this->updateat = $updateat;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getCreateby(): ?User
    {
        return $this->createby;
    }

    public function setCreateby(?User $createby): self
    {
        $this->createby = $createby;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getTypechambre(): ?Typechambre
    {
        return $this->typechambre;
    }

    public function setTypechambre(?Typechambre $typechambre): self
    {
        $this->typechambre = $typechambre;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

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

    
}
