<?php

namespace App\Entity;

use App\Repository\InterventionConsumedPartRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: InterventionConsumedPartRepository::class)]
class InterventionConsumedPart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'interventionConsumedParts')]
    private ?Intervention $intervention = null;

    #[ORM\ManyToOne(inversedBy: 'interventionConsumedParts')]
    private ?Part $part = null;

    #[ORM\Column]
    private ?int $quantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntervention(): ?Intervention
    {
        return $this->intervention;
    }

    public function setIntervention(?Intervention $intervention): static
    {
        $this->intervention = $intervention;

        return $this;
    }

    public function getPart(): ?Part
    {
        return $this->part;
    }

    public function setPart(?Part $part): static
    {
        $this->part = $part;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    #[Assert\Callback]
    public function validateStock(ExecutionContextInterface $context): void
    {
        // On vérifie si la quantité saisie est supérieure au stock actuel de la pièce
        if ($this->getPart() !== null && $this->getQuantity() > $this->getPart()->getStockQuantity()) {
            $context->buildViolation('Stock insuffisant ! Il ne reste que {{ limit }} unité(s).')
                ->setParameter('{{ limit }}', (string) $this->getPart()->getStockQuantity())
                ->atPath('quantity') // L'erreur s'affichera sur le champ quantité
                ->addViolation();
        }
    }
}
