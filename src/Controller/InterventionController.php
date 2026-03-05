<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\Machine;
use App\Form\InterventionType;
use App\Repository\InterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/intervention')]
final class InterventionController extends AbstractController
{
    #[Route(name: 'app_intervention_index', methods: ['GET'])]
    public function index(InterventionRepository $interventionRepository): Response
    {
        return $this->render('intervention/index.html.twig', [
            'interventions' => $interventionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_intervention_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $intervention = new Intervention();

        $machineId = $request->query->get('machine');
        if ($machineId) {
            $machine = $entityManager->getRepository(Machine::class)->find($machineId);
            if ($machine) {
                $intervention->setMachine($machine);
            }
        }

        $form = $this->createForm(InterventionType::class, $intervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // --- DÉBUT DE LA LOGIQUE DE STOCK ---
            foreach ($intervention->getInterventionConsumedParts() as $consumedPart) {
                $part = $consumedPart->getPart();
                $quantityUsed = $consumedPart->getQuantity();

                if ($part) {
                    // On soustrait la quantité utilisée au stock actuel
                    $newStock = $part->getStockQuantity() - $quantityUsed;
                    $part->setStockQuantity($newStock);

                    // On persist explicitement la pièce pour que Doctrine sache qu'elle a changé
                    $entityManager->persist($part);
                }
            }
            // --- FIN DE LA LOGIQUE DE STOCK ---

            $entityManager->persist($intervention);
            $entityManager->flush();

            return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intervention/new.html.twig', [
            'intervention' => $intervention,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_intervention_show', methods: ['GET'])]
    public function show(Intervention $intervention): Response
    {
        return $this->render('intervention/show.html.twig', [
            'intervention' => $intervention,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_intervention_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Intervention $intervention, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InterventionType::class, $intervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intervention/edit.html.twig', [
            'intervention' => $intervention,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_intervention_delete', methods: ['POST'])]
    public function delete(Request $request, Intervention $intervention, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$intervention->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($intervention);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/close', name: 'app_intervention_close', methods: ['POST', 'GET'])]
    public function close(Intervention $intervention, EntityManagerInterface $entityManager): Response
    {
        if (!$intervention->getEndedAt()) {

            // --- 1. ÉTAPE DE VÉRIFICATION ---
            foreach ($intervention->getInterventionConsumedParts() as $consumedPart) {
                $part = $consumedPart->getPart();
                $quantityNeeded = $consumedPart->getQuantity();

                if ($part && $part->getStockQuantity() < $quantityNeeded) {
                    // ALERTE : Stock insuffisant !
                    $this->addFlash('danger', sprintf(
                        'Impossible de clôturer : Stock insuffisant pour la pièce "%s" (Disponible : %d, Requis : %d)',
                        $part->getDesignation(),
                        $part->getStockQuantity(),
                        $quantityNeeded
                    ));

                    // On redirige vers l'édition pour que le tech puisse corriger
                    return $this->redirectToRoute('app_intervention_edit', ['id' => $intervention->getId()]);
                }
            }

            // --- 2. ÉTAPE DE DÉSTOCKAGE (si tout est OK) ---
            foreach ($intervention->getInterventionConsumedParts() as $consumedPart) {
                $part = $consumedPart->getPart();
                if ($part) {
                    $part->setStockQuantity($part->getStockQuantity() - $consumedPart->getQuantity());
                }
            }

            $intervention->setEndedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Intervention clôturée et stocks mis à jour !');
        }

        return $this->redirectToRoute('app_machine_show', ['id' => $intervention->getMachine()->getId()]);
    }

    #[Route('/{id}/pdf', name: 'app_intervention_pdf', methods: ['GET'])]
    public function generatePdf(Intervention $intervention): Response
    {
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);

        // Initialise Dompdf
        $dompdf = new Dompdf($pdfOptions);

        // Génère le HTML à partir du Twig
        $html = $this->renderView('intervention/pdf.html.twig', [
            'intervention' => $intervention,
        ]);

        // Charge le HTML dans Dompdf
        $dompdf->loadHtml($html);

        // (Optionnel) Taille du papier et orientation
        $dompdf->setPaper('A4', 'portrait');

        // Rendu du PDF
        $dompdf->render();

        // Envoi du PDF au navigateur
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="intervention-'.$intervention->getId().'.pdf"'
        ]);
    }
}
