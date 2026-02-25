<?php

namespace App\Entity;

use App\Repository\PartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartRepository::class)]
class Part
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $designation = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[ORM\Column]
    private ?int $stockQuantity = null;

    #[ORM\ManyToOne(inversedBy: 'parts')]
    private ?Supplier $supplier = null;

    /**
     * @var Collection<int, Machine>
     */
    #[ORM\ManyToMany(targetEntity: Machine::class, inversedBy: 'parts')]
    private Collection $machines;

    /**
     * @var Collection<int, InterventionConsumedPart>
     */
    #[ORM\OneToMany(targetEntity: InterventionConsumedPart::class, mappedBy: 'part')]
    private Collection $interventionConsumedParts;

    public function __construct()
    {
        $this->machines = new ArrayCollection();
        $this->interventionConsumedParts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    public function setStockQuantity(int $stockQuantity): static
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * @return Collection<int, Machine>
     */
    public function getMachines(): Collection
    {
        return $this->machines;
    }

    public function addMachine(Machine $machine): static
    {
        if (!$this->machines->contains($machine)) {
            $this->machines->add($machine);
        }

        return $this;
    }

    public function removeMachine(Machine $machine): static
    {
        $this->machines->removeElement($machine);

        return $this;
    }

    /**
     * @return Collection<int, InterventionConsumedPart>
     */
    public function getInterventionConsumedParts(): Collection
    {
        return $this->interventionConsumedParts;
    }

    public function addInterventionConsumedPart(InterventionConsumedPart $interventionConsumedPart): static
    {
        if (!$this->interventionConsumedParts->contains($interventionConsumedPart)) {
            $this->interventionConsumedParts->add($interventionConsumedPart);
            $interventionConsumedPart->setPart($this);
        }

        return $this;
    }

    public function removeInterventionConsumedPart(InterventionConsumedPart $interventionConsumedPart): static
    {
        if ($this->interventionConsumedParts->removeElement($interventionConsumedPart)) {
            // set the owning side to null (unless already changed)
            if ($interventionConsumedPart->getPart() === $this) {
                $interventionConsumedPart->setPart(null);
            }
        }

        return $this;
    }
}
