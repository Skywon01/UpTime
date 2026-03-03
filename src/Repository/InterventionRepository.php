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
}
