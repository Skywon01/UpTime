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
        $user = $this->getUser();
        $company = $user->getCompany();

        // On récupère les interventions finies pour calculer le coût total en PHP
        $finishedInterventions = $interRepo->findAllFinishedByCompany($company);
        $totalCost = 0;
        foreach ($finishedInterventions as $intervention) {
            $totalCost += $intervention->getTotalCost();
        }

        return $this->render('dashboard/index.html.twig', [
            'company' => $company,
            'totalMachines' => $machineRepo->count(['company' => $company]),
            'availableMachines' => $machineRepo->count(['company' => $company, 'status' => 'Opérationnel']),
            'lowStockParts' => $partRepo->findLowStockPartsByCompany($company, 5),
            'brokenMachines' => $machineRepo->count(['company' => $company, 'status' => 'en panne']),

            'totalCost' => $totalCost,
            'activeInterventionsCount' => $interRepo->countActiveByCompany($company),
            'topMachines' => $machineRepo->findTopMachinesByCompany($company, 5),
            // On appelle les méthodes du Repository qu'on va créer juste après
            'recentInterventions' => $interRepo->findRecentByCompany($company, 5),
            'upcomingMaintenances' => $machineRepo->findUpcomingMaintenancesByCompany($company),
            'lateMaintenances' => $machineRepo->findLateMaintenancesByCompany($company),
        ]);
    }

    #[Route('/stats', name: 'app_stats')]
    public function stats(InterventionRepository $repo): Response
    {
        $user = $this->getUser();
        $company = $user->getCompany();

        // IMPORTANT : On ne fait plus un findAll(), on filtre par entreprise !
        $interventions = $repo->findAllFinishedByCompany($company);

        $totalGlobalCost = 0;
        $totalHours = 0;
        $interventionCount = count($interventions);
        $preventiveCount = 0;
        $machineCosts = [];

        foreach ($interventions as $intervention) {
            $cost = $intervention->getTotalCost();
            $totalGlobalCost += $cost;
            $totalHours += $intervention->getDurationInHours();

            if ($intervention->getType() === 'Préventif') {
                $preventiveCount++;
            }

            $name = $intervention->getMachine() ? $intervention->getMachine()->getName() : 'N/A';
            $machineCosts[$name] = ($machineCosts[$name] ?? 0) + $cost;
        }

        arsort($machineCosts);
        $topMachines = array_slice($machineCosts, 0, 5);
        $preventiveRate = $interventionCount > 0 ? round(($preventiveCount / $interventionCount) * 100) : 0;

        // Stats par type filtrées par entreprise
        $statsType = $repo->countByTypeByCompany($company);

        return $this->render('dashboard/stats.html.twig', [
            'totalGlobalCost' => $totalGlobalCost,
            'totalHours' => $totalHours,
            'interventionCount' => $interventionCount,
            'preventiveRate' => $preventiveRate,
            'topMachines' => $topMachines,
            'statsType' => $statsType,
        ]);
    }
}
