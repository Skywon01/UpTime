<?php

namespace App\Repository;

use App\Entity\Company;
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

    public function getTotalCostByCompany(Company $company)
    {
        // On additionne chaque champ séparément dans la requête
        return $this->createQueryBuilder('i')
            ->select('SUM(i.partsPrice) + SUM(i.laborPrice) + SUM(i.downtimeCost) as total')
            ->where('i.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getSingleScalarResult() ?: 0;
    }

    public function findAllFinishedByCompany($company): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.machine', 'm')
            ->where('m.company = :company')
            ->andWhere('i.endedAt IS NOT NULL')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    public function countByTypeByCompany($company): array
    {
        return $this->createQueryBuilder('i')
            ->select('i.type, COUNT(i.id) as nombre')
            ->join('i.machine', 'm')
            ->where('m.company = :company')
            ->setParameter('company', $company)
            ->groupBy('i.type')
            ->getQuery()
            ->getResult();
    }

    public function countActiveByCompany($company): int
    {
        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->join('i.machine', 'm')
            ->where('m.company = :company')
            ->andWhere('i.endedAt IS NULL')
            ->setParameter('company', $company)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRecentByCompany($company, int $limit = 5): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.machine', 'm') // On doit joindre la machine pour filtrer par entreprise
            ->where('m.company = :company')
            ->setParameter('company', $company)
            ->orderBy('i.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }


}
