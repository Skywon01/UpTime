<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Machine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Machine>
 */
class MachineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Machine::class);
    }

    public function findTopMachines(int $limit = 5): array
    {
        return $this->createQueryBuilder('m')
            ->select('m as machine, COUNT(i.id) as interventionCount')
            ->leftJoin('m.interventions', 'i')
            ->groupBy('m.id')
            ->orderBy('interventionCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Machines ayant une maintenance prévue dans les 7 prochains jours
     */
    public function findUpcomingMaintenances(int $days = 7): array
    {
        $now = new \DateTimeImmutable();
        $nextLimit = $now->modify('+' . $days . ' days');

        return $this->createQueryBuilder('m')
            ->where('m.nextMaintenanceAt BETWEEN :now AND :nextLimit')
            ->setParameter('now', $now)
            ->setParameter('nextLimit', $nextLimit)
            ->orderBy('m.nextMaintenanceAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Machines dont la date de maintenance est dépassée
     */
    public function findLateMaintenances(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.nextMaintenanceAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('m.nextMaintenanceAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLateMaintenancesByCompany($company): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.company = :company')
            ->andWhere('m.nextMaintenanceAt < :now')
            ->setParameter('company', $company)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('m.nextMaintenanceAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingMaintenancesByCompany($company): array
    {
        $now = new \DateTimeImmutable();
        return $this->createQueryBuilder('m')
            ->where('m.company = :company')
            ->andWhere('m.nextMaintenanceAt >= :now')
            ->andWhere('m.nextMaintenanceAt <= :limit')
            ->setParameter('company', $company)
            ->setParameter('now', $now)
            ->setParameter('limit', $now->modify('+15 days'))
            ->orderBy('m.nextMaintenanceAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTopMachinesByCompany(Company $company, int $limit = 5): array
    {
        return $this->createQueryBuilder('m')
            // On sélectionne la machine et on compte ses interventions
            ->select('m as machine, COUNT(i.id) as interventionCount')
            ->leftJoin('m.interventions', 'i')
            ->where('m.company = :company') // LE CLOISONNEMENT
            ->setParameter('company', $company)
            ->groupBy('m.id')
            ->orderBy('interventionCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
