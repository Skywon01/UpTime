<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Machine $machine = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    private ?User $technician = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    /**
     * @var Collection<int, InterventionConsumedPart>
     */
    #[ORM\OneToMany(targetEntity: InterventionConsumedPart::class, mappedBy: 'intervention', cascade: ['persist'])]
    private Collection $interventionConsumedParts;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    public function setMachine(?Machine $machine): static
    {
        $this->machine = $machine;

        return $this;
    }

    public function getTechnician(): ?User
    {
        return $this->technician;
    }

    public function setTechnician(?User $technician): static
    {
        $this->technician = $technician;

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

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->interventionConsumedParts = new ArrayCollection();
    }

    public function getDuration(): ?\DateInterval
    {
        if (!$this->createdAt || !$this->endedAt) {
            return null;
        }
        return $this->createdAt->diff($this->endedAt);
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

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
            $interventionConsumedPart->setIntervention($this);
        }

        return $this;
    }

    public function removeInterventionConsumedPart(InterventionConsumedPart $interventionConsumedPart): static
    {
        if ($this->interventionConsumedParts->removeElement($interventionConsumedPart)) {
            // set the owning side to null (unless already changed)
            if ($interventionConsumedPart->getIntervention() === $this) {
                $interventionConsumedPart->setIntervention(null);
            }
        }

        return $this;
    }

    public function getTotalPartsCost(): float
    {
        $total = 0;
        foreach ($this->interventionConsumedParts as $consumedPart) {
            $total += $consumedPart->getPart()->getPrice() * $consumedPart->getQuantity();
        }
        return $total;
    }

    /**
     * Calcule la durée en heures (utile pour le coût d'arrêt futur)
     */
    public function getDurationInHours(): float
    {
        if (!$this->getCreatedAt() || !$this->getEndedAt()) {
            return 0;
        }

        $diff = $this->getCreatedAt()->diff($this->getEndedAt());

        // Conversion totale en heures (heures + minutes/60 + jours*24)
        $hours = $diff->h + ($diff->i / 60) + ($diff->days * 24);

        return round($hours, 2);
    }
}
