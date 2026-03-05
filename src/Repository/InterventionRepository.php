<?php

namespace App\Repository;

use App\Entity\Intervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intervention>
 */
class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    public function getTotalCost(): float
    {
        // On utilise DQL pour sommer les prix des pièces liées aux interventions
        return (float) $this->createQueryBuilder('i')
            ->join('i.interventionConsumedParts', 'icp')
            ->join('icp.part', 'p')
            ->select('SUM(p.price * icp.quantity)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAverageRepairTime(): float
    {
        $qb = $this->createQueryBuilder('i')
            ->select('AVG(DATE_DIFF(i.endedAt, i.createdAt))')
            ->where('i.endedAt IS NOT NULL');

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    public function findFilteredInterventions(?int $machineId, ?string $status, ?string $type): array
    {
        $qb = $this->createQueryBuilder('i')
            ->orderBy('i.createdAt', 'DESC');

        // Filtre par Machine
        if ($machineId) {
            $qb->andWhere('i.machine = :machineId')
                ->setParameter('machineId', $machineId);
        }

        // Filtre par Statut
        if ($status === 'active') {
            $qb->andWhere('i.endedAt IS NULL');
        } elseif ($status === 'finished') {
            $qb->andWhere('i.endedAt IS NOT NULL');
        }

        // Filtre par Type
        if ($type) {
            $qb->andWhere('i.type = :type')
                ->setParameter('type', $type);
        }

        return $qb->getQuery()->getResult();
    }
}
