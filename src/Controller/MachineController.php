<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Form\MachineType;
use App\Repository\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


#[Route('/machine')]
final class MachineController extends AbstractController
{
    #[Route(name: 'app_machine_index', methods: ['GET'])]
    public function index(MachineRepository $machineRepository): Response
    {
        return $this->render('machine/index.html.twig', [
            'machines' => $machineRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_machine_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $machine = new Machine();
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($machine);
            $entityManager->flush();

            return $this->redirectToRoute('app_machine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('machine/new.html.twig', [
            'machine' => $machine,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_machine_show', methods: ['GET'])]
    public function show(Machine $machine): Response
    {
        return $this->render('machine/show.html.twig', [
            'machine' => $machine,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_machine_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Machine $machine, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_machine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('machine/edit.html.twig', [
            'machine' => $machine,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_machine_delete', methods: ['POST'])]
    public function delete(Request $request, Machine $machine, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$machine->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($machine);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_machine_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/qrcode', name: 'app_machine_qr_code', methods: ['GET'])]
    public function generateQrCode(Machine $machine, Request $request): Response
    {

        $host = $request->getHttpHost();
        // URL absolue vers la fiche de la machine
        $url = $request->getScheme() . '://' . $host . $this->generateUrl(
                'app_machine_show',
                ['id' => $machine->getId()]
            );

        // Syntaxe v6 avec arguments nommés
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $result = $builder->build();

        return new Response(
            $result->getString(),
            200,
            ['Content-Type' => $result->getMimeType()]
        );
    }



}
