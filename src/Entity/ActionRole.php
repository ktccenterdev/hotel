<?php

namespace App\Entity;

use App\Repository\ActionRoleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActionRoleRepository::class)
 */
class ActionRole
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $etat;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isdefault;

    


    /**
     * @ORM\ManyToOne(targetEntity=Role::class, inversedBy="actionRoles")
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity=Action::class, inversedBy="actionRoles")
     */
    private $action;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtat(): ?bool
    {
        return $this->etat;
    }

    public function setEtat(bool $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getIsdefault(): ?bool
    {
        return $this->isdefault;
    }

    public function setIsdefault(?bool $isdefault): self
    {
        $this->isdefault = $isdefault;

        return $this;
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

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(?Action $action): self
    {
        $this->action = $action;

        return $this;
    }
}
