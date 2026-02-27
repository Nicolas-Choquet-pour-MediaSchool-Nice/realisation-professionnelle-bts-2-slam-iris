<?php

namespace App\Entity;

use App\Repository\SchoolClassRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SchoolClassRepository::class)]
#[UniqueEntity(fields: ['name'], message: 'Cette classe existe déjà.')]
class SchoolClass
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    /**
     * @var Collection<int, Student>
     */
    #[ORM\OneToMany(targetEntity: Student::class, mappedBy: 'class')]
    private Collection $students;

    /**
     * @var Collection<int, Coordinator>
     */
    #[ORM\ManyToMany(targetEntity: Coordinator::class, mappedBy: 'managedClasses')]
    private Collection $coordinators;

    public function __construct()
    {
        $this->students = new ArrayCollection();
        $this->coordinators = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Student>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Student $student): static
    {
        if (!$this->students->contains($student)) {
            $this->students->add($student);
            $student->setClass($this);
        }

        return $this;
    }

    public function removeStudent(Student $student): static
    {
        if ($this->students->removeElement($student)) {
            // set the owning side to null (unless already changed)
            if ($student->getClass() === $this) {
                $student->setClass(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Coordinator>
     */
    public function getCoordinators(): Collection
    {
        return $this->coordinators;
    }

    public function addCoordinator(Coordinator $coordinator): static
    {
        if (!$this->coordinators->contains($coordinator)) {
            $this->coordinators->add($coordinator);
            $coordinator->addManagedClass($this);
        }

        return $this;
    }

    public function removeCoordinator(Coordinator $coordinator): static
    {
        if ($this->coordinators->removeElement($coordinator)) {
            $coordinator->removeManagedClass($this);
        }

        return $this;
    }
}
