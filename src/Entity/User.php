<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\ManyToOne(targetEntity=Role::class, inversedBy="users")
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prenom;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $datenaisance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sexe;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cni;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lieunaisance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $etatcivil;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $profession;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nationalite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adresse;

    /**
     * @ORM\ManyToOne(targetEntity=Entene::class, inversedBy="users")
     */
    private $antene;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity=Allocation::class, mappedBy="occupant")
     */
    private $allocations;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="client")
     */
    private $reservations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titel;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="createby")
     */
    private $reservationsaves;

    /**
     * @ORM\OneToMany(targetEntity=Log::class, mappedBy="operateur")
     */
    private $logs;

    /**
     * @ORM\OneToMany(targetEntity=Entrestock::class, mappedBy="user")
     */
    private $entrestocks;

    /**
     * @ORM\OneToMany(targetEntity=Sortirstock::class, mappedBy="user")
     */
    private $sortirstocks;

    /**
     * @ORM\OneToMany(targetEntity=SortieFinanciere::class, mappedBy="operateur")
     */
    private $sortieFinancieres;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $solde;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="createdby")
     */
    private $transactions;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="client")
     */
    private $clienttransactions;

    /**
     * @ORM\OneToMany(targetEntity=Note::class, mappedBy="createdby")
     */
    private $notescreate;

    /**
     * @ORM\OneToMany(targetEntity=Note::class, mappedBy="clientnote")
     */
    private $clientnote;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isadmin;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isdeleted;

    public function __construct()
    {
        $this->allocations = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->reservationsaves = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->entrestocks = new ArrayCollection();
        $this->sortirstocks = new ArrayCollection();
        $this->sortieFinancieres = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->clienttransactions = new ArrayCollection();
        $this->notescreate = new ArrayCollection();
        $this->clientnote = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getDatenaisance(): ?\DateTimeInterface
    {
        return $this->datenaisance;
    }

    public function setDatenaisance(?\DateTimeInterface $datenaisance): self
    {
        $this->datenaisance = $datenaisance;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getCni(): ?string
    {
        return $this->cni;
    }

    public function setCni(?string $cni): self
    {
        $this->cni = $cni;

        return $this;
    }

    public function getLieunaisance(): ?string
    {
        return $this->lieunaisance;
    }

    public function setLieunaisance(?string $lieunaisance): self
    {
        $this->lieunaisance = $lieunaisance;

        return $this;
    }

    public function getEtatcivil(): ?string
    {
        return $this->etatcivil;
    }

    public function setEtatcivil(?string $etatcivil): self
    {
        $this->etatcivil = $etatcivil;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): self
    {
        $this->profession = $profession;

        return $this;
    }

    public function getNationalite(): ?string
    {
        return $this->nationalite;
    }

    public function setNationalite(?string $nationalite): self
    {
        $this->nationalite = $nationalite;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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
            $allocation->setOccupant($this);
        }

        return $this;
    }

    public function removeAllocation(Allocation $allocation): self
    {
        if ($this->allocations->removeElement($allocation)) {
            // set the owning side to null (unless already changed)
            if ($allocation->getOccupant() === $this) {
                $allocation->setOccupant(null);
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
            $reservation->setClient($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getClient() === $this) {
                $reservation->setClient(null);
            }
        }

        return $this;
    }

    public function getTitel(): ?string
    {
        return $this->titel;
    }

    public function setTitel(?string $titel): self
    {
        $this->titel = $titel;

        return $this;
    }

    /**
     * @return Collection|Reservation[]
     */
    public function getReservationsaves(): Collection
    {
        return $this->reservationsaves;
    }

    public function addReservationsafe(Reservation $reservationsafe): self
    {
        if (!$this->reservationsaves->contains($reservationsafe)) {
            $this->reservationsaves[] = $reservationsafe;
            $reservationsafe->setCreateby($this);
        }

        return $this;
    }

    public function removeReservationsafe(Reservation $reservationsafe): self
    {
        if ($this->reservationsaves->removeElement($reservationsafe)) {
            // set the owning side to null (unless already changed)
            if ($reservationsafe->getCreateby() === $this) {
                $reservationsafe->setCreateby(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Log[]
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setOperateur($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getOperateur() === $this) {
                $log->setOperateur(null);
            }
        }

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
            $entrestock->setUser($this);
        }

        return $this;
    }

    public function removeEntrestock(Entrestock $entrestock): self
    {
        if ($this->entrestocks->removeElement($entrestock)) {
            // set the owning side to null (unless already changed)
            if ($entrestock->getUser() === $this) {
                $entrestock->setUser(null);
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
            $sortirstock->setUser($this);
        }

        return $this;
    }

    public function removeSortirstock(Sortirstock $sortirstock): self
    {
        if ($this->sortirstocks->removeElement($sortirstock)) {
            // set the owning side to null (unless already changed)
            if ($sortirstock->getUser() === $this) {
                $sortirstock->setUser(null);
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
            $sortieFinanciere->setOperateur($this);
        }

        return $this;
    }

    public function removeSortieFinanciere(SortieFinanciere $sortieFinanciere): self
    {
        if ($this->sortieFinancieres->removeElement($sortieFinanciere)) {
            // set the owning side to null (unless already changed)
            if ($sortieFinanciere->getOperateur() === $this) {
                $sortieFinanciere->setOperateur(null);
            }
        }

        return $this;
    }

    public function hasAccess($droit){
        $droits = array();
        foreach ($this->getRole()->getActionRoles() as $item) {
            if($item->getEtat()){
                array_push($droits, $item->getAction()->getCle());
            }            
        }
        if(!in_array($droit, $droits)){
            //return "d-none";
        }
        return;
    }

    public function getSolde(): ?float
    {
        return $this->solde;
    }

    public function setSolde(?float $solde): self
    {
        $this->solde = $solde;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setCreatedby($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getCreatedby() === $this) {
                $transaction->setCreatedby(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getClienttransactions(): Collection
    {
        return $this->clienttransactions;
    }

    public function addClienttransaction(Transaction $clienttransaction): self
    {
        if (!$this->clienttransactions->contains($clienttransaction)) {
            $this->clienttransactions[] = $clienttransaction;
            $clienttransaction->setClient($this);
        }

        return $this;
    }

    public function removeClienttransaction(Transaction $clienttransaction): self
    {
        if ($this->clienttransactions->removeElement($clienttransaction)) {
            // set the owning side to null (unless already changed)
            if ($clienttransaction->getClient() === $this) {
                $clienttransaction->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Note[]
     */
    public function getNotescreate(): Collection
    {
        return $this->notescreate;
    }

    public function addNotescreate(Note $notescreate): self
    {
        if (!$this->notescreate->contains($notescreate)) {
            $this->notescreate[] = $notescreate;
            $notescreate->setCreatedby($this);
        }

        return $this;
    }

    public function removeNotescreate(Note $notescreate): self
    {
        if ($this->notescreate->removeElement($notescreate)) {
            // set the owning side to null (unless already changed)
            if ($notescreate->getCreatedby() === $this) {
                $notescreate->setCreatedby(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Note[]
     */
    public function getClientnote(): Collection
    {
        return $this->clientnote;
    }

    public function addClientnote(Note $clientnote): self
    {
        if (!$this->clientnote->contains($clientnote)) {
            $this->clientnote[] = $clientnote;
            $clientnote->setClientnote($this);
        }

        return $this;
    }

    public function removeClientnote(Note $clientnote): self
    {
        if ($this->clientnote->removeElement($clientnote)) {
            // set the owning side to null (unless already changed)
            if ($clientnote->getClientnote() === $this) {
                $clientnote->setClientnote(null);
            }
        }

        return $this;
    }

    public function getIsadmin(): ?bool
    {
        return $this->isadmin;
    }

    public function setIsadmin(bool $isadmin): self
    {
        $this->isadmin = $isadmin;

        return $this;
    }

    public function getIsdeleted(): ?bool
    {
        return $this->isdeleted;
    }

    public function setIsdeleted(?bool $isdeleted): self
    {
        $this->isdeleted = $isdeleted;

        return $this;
    }
}
