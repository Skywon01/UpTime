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

        // 1. Les maintenances en retard (Date passée)
        $lateMaintenances = $machineRepo->createQueryBuilder('m')
            ->where('m.nextMaintenanceAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();

// 2. Les maintenances à venir (7 prochains jours par exemple)
        $upcomingMaintenances = $machineRepo->createQueryBuilder('m')
            ->where('m.nextMaintenanceAt BETWEEN :now AND :nextWeek')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('nextWeek', new \DateTimeImmutable('+7 days'))
            ->getQuery()
            ->getResult();

        return $this->render('dashboard/index.html.twig', [
            // Récupérer les données pour les statistiques
            'totalMachines' => $machineRepo->count([]),
            'brokenMachines' => $machineRepo->count(['status' => 'en panne']),

            'lowStockParts' => $partRepo->findByLowStock(5),

            'recentInterventions' => $interRepo->findBy([], ['createdAt' => 'DESC'], 5),

            'topMachines' => array_slice($machineStats, 0, 5),
            'availableMachines' => $availableMachines,

            'upcomingMaintenances' => $upcomingMaintenances,

            'lateMaintenances' => $lateMaintenances,
        ]);


    }
}
