<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RoleRepository::class)
 */
class Role
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isdefauld;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateAt;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="role")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=ActionRole::class, mappedBy="role")
     */
    private $actionRoles;





    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->actionRoles = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIsdefauld(): ?bool
    {
        return $this->isdefauld;
    }

    public function setIsdefauld(?bool $isdefauld): self
    {
        $this->isdefauld = $isdefauld;

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
            $user->setRole($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getRole() === $this) {
                $user->setRole(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ActionRole[]
     */
    public function getActionRoles(): Collection
    {
        return $this->actionRoles;
    }

    public function addActionRole(ActionRole $actionRole): self
    {
        if (!$this->actionRoles->contains($actionRole)) {
            $this->actionRoles[] = $actionRole;
            $actionRole->setRole($this);
        }

        return $this;
    }

    public function removeActionRole(ActionRole $actionRole): self
    {
        if ($this->actionRoles->removeElement($actionRole)) {
            // set the owning side to null (unless already changed)
            if ($actionRole->getRole() === $this) {
                $actionRole->setRole(null);
            }
        }

        return $this;
    }
    
}
