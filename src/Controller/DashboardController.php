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
    #[Route('/dashboard', name: 'app_dashboard')]


    public function index(
        MachineRepository $machineRepo,
        PartRepository $partRepo,
        InterventionRepository $interRepo
    ): Response {
        $allMachines = $machineRepo->findAll();
        $machineStats = [];

        foreach ($allMachines as $machine) {
            $machineStats[] = [
                'name' => $machine->getName(), // Assure-toi que la méthode existe (ou getName())
                'interventionCount' => $machine->getInterventions()->count(),
                'status' => $machine->getStatus(),
            ];
        }

        // Tri décroissant : les plus sollicitées en haut
        usort($machineStats, fn($a, $b) => $b['interventionCount'] <=> $a['interventionCount']);
        $availableMachines = $machineRepo->count(['status' => 'Opérationnel']);
        return $this->render('dashboard/index.html.twig', [
            // Récupérer les données pour les statistiques
            'totalMachines' => $machineRepo->count([]),
            'brokenMachines' => $machineRepo->count(['status' => 'en panne']),

            'lowStockParts' => $partRepo->findByLowStock(5),

            'recentInterventions' => $interRepo->findBy([], ['createdAt' => 'DESC'], 5),

            'topMachines' => array_slice($machineStats, 0, 5),
            'availableMachines' => $availableMachines,
        ]);


    }
}
