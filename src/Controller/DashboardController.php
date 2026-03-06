<?php

namespace App\Controller;

use App\Repository\InterventionRepository;
use App\Repository\MachineRepository;
use App\Repository\PartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard_index')]


    public function index(
        MachineRepository $machineRepo,
        PartRepository $partRepo,
        InterventionRepository $interRepo
    ): Response {
        return $this->render('dashboard/index.html.twig', [
            'totalMachines' => $machineRepo->count([]),
            'availableMachines' => $machineRepo->count(['status' => 'Opérationnel']),
            'brokenMachines' => $machineRepo->count(['status' => 'en panne']),

            // On délègue les calculs complexes aux repositories
            'totalCost' => $interRepo->getTotalCost(),
            'topMachines' => $machineRepo->findTopMachines(5),
            'lowStockParts' => $partRepo->findLowStockParts(5),

            // Count simple via QueryBuilder
            'activeInterventionsCount' => $interRepo->count(['endedAt' => null]),

            // Les listes avec filtres
            'recentInterventions' => $interRepo->findBy([], ['createdAt' => 'DESC'], 5),
            'upcomingMaintenances' => $machineRepo->findUpcomingMaintenances(), // À créer dans le repo
            'lateMaintenances' => $machineRepo->findLateMaintenances(),         // À créer dans le repo
        ]);

    }

    // src/Controller/DashboardController.php

    #[Route('/stats', name: 'app_stats')]
    public function stats(InterventionRepository $repo): Response
    {
        $interventions = $repo->findAll();

        $totalGlobalCost = 0;
        $totalHours = 0;
        $interventionCount = count($interventions);
        $preventiveCount = 0;

        $machineCosts = [];

        foreach ($interventions as $intervention) {
            // Calcul du coût réel par intervention
            $cost = $intervention->getTotalPartsCost()
                + $intervention->getTotalLaborCost()
                + $intervention->getTotalDowntimeCost();

            $totalGlobalCost += $cost;
            $totalHours += $intervention->getDurationInHours();

            if ($intervention->getType() === 'Préventif') {
                $preventiveCount++;
            }

            // Top 5 Machines logic
            $name = $intervention->getMachine() ? $intervention->getMachine()->getName() : 'N/A';
            $machineCosts[$name] = ($machineCosts[$name] ?? 0) + $cost;
        }

        arsort($machineCosts);
        $topMachines = array_slice($machineCosts, 0, 5);

        // Ratio préventif en %
        $preventiveRate = $interventionCount > 0 ? round(($preventiveCount / $interventionCount) * 100) : 0;

        $statsType = $repo->createQueryBuilder('i')
            ->select('i.type, COUNT(i.id) as nombre')
            ->groupBy('i.type')
            ->getQuery()
            ->getResult();

        return $this->render('dashboard/stats.html.twig', [
            'totalGlobalCost' => $totalGlobalCost,
            'totalHours' => $totalHours,
            'interventionCount' => $interventionCount,
            'preventiveRate' => $preventiveRate,
            'topMachines' => $topMachines,
            'statsType' => $statsType,
            // ... (tes autres variables pour les charts)
        ]);
    }

    /**
     * Machines dont la maintenance est dépassée (Date < Aujourd'hui)
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

    /**
     * Machines dont la maintenance arrive bientôt (entre aujourd'hui et +15 jours)
     * @throws \DateMalformedStringException
     */
    public function findUpcomingMaintenances(): array
    {
        $now = new \DateTimeImmutable();
        $limit = $now->modify('+15 days');

        return $this->createQueryBuilder('m')
            ->where('m.nextMaintenanceAt >= :now')
            ->andWhere('m.nextMaintenanceAt <= :limit')
            ->setParameter('now', $now)
            ->setParameter('limit', $limit)
            ->orderBy('m.nextMaintenanceAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
