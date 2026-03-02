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
        // On garde tes logiques de stats existantes
        $allMachines = $machineRepo->findAll();
        $machineStats = [];
        foreach ($allMachines as $machine) {
            $machineStats[] = [
                'name' => $machine->getName(),
                'interventionCount' => $machine->getInterventions()->count(),
                'status' => $machine->getStatus(),
            ];
        }
        usort($machineStats, fn($a, $b) => $b['interventionCount'] <=> $a['interventionCount']);

        // NOUVEAU : Calcul du coût total de toutes les interventions
        $allInterventions = $interRepo->findAll();
        $totalCost = 0;
        foreach ($allInterventions as $inter) {
            $totalCost += $inter->getTotalPartsCost();
        }

        // NOUVEAU : Interventions en cours (pas de endedAt)
        $activeInterventionsCount = $interRepo->createQueryBuilder('i')
            ->select('count(i.id)')
            ->where('i.endedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('dashboard/index.html.twig', [
            'totalMachines' => $machineRepo->count([]),
            'brokenMachines' => $machineRepo->count(['status' => 'en panne']),
            'activeInterventionsCount' => $activeInterventionsCount, // Ajouté
            'totalCost' => $totalCost, // Ajouté
            'lowStockParts' => $partRepo->findByLowStock(5),
            'recentInterventions' => $interRepo->findBy([], ['createdAt' => 'DESC'], 5),
            'topMachines' => array_slice($machineStats, 0, 5),
            'availableMachines' => $machineRepo->count(['status' => 'Opérationnel']),
            'upcomingMaintenances' => $machineRepo->createQueryBuilder('m')
                ->where('m.nextMaintenanceAt BETWEEN :now AND :nextWeek')
                ->setParameter('now', new \DateTimeImmutable())
                ->setParameter('nextWeek', new \DateTimeImmutable('+7 days'))
                ->getQuery()->getResult(),
            'lateMaintenances' => $machineRepo->createQueryBuilder('m')
                ->where('m.nextMaintenanceAt < :now')
                ->setParameter('now', new \DateTimeImmutable())
                ->getQuery()->getResult(),
        ]);
    }
}
