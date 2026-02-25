<?php

namespace App\Entity;

use App\Repository\CoordinatorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoordinatorRepository::class)]
class Coordinator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, SchoolClass>
     */
    #[ORM\ManyToMany(targetEntity: SchoolClass::class, inversedBy: 'coordinators')]
    private Collection $managedClasses;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'coordinator')]
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function __construct()
    {
        $this->managedClasses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    public function getManagedClasses(): Collection
    {
        return $this->managedClasses;
    }

    public function addManagedClass(SchoolClass $managedClass): static
    {
        if (!$this->managedClasses->contains($managedClass)) {
            $this->managedClasses->add($managedClass);
        }

        return $this;
    }

    public function removeManagedClass(SchoolClass $managedClass): static
    {
        $this->managedClasses->removeElement($managedClass);

        return $this;
    }
}
