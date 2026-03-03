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
}
