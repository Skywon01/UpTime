<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\User;
use App\Form\UserType; // <-- Ne pas oublier l'import du formulaire
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/god')]
final class GodController extends AbstractController
{
    // J'ai raccourci le chemin ici car le /god est déjà dans le préfixe de la classe
    #[Route('/company/{id}/add-user', name: 'app_god_company_add_user', methods: ['GET', 'POST'])]
    public function addUserToCompany(
        Company $company,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        // On pré-remplit l'entreprise pour le formulaire
        $user->setCompany($company);

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hashage du mot de passe saisi
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            // Attribution du rôle spécifique au client
            $user->setRoles(['ROLE_COMPANY_ADMIN']);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', "L'accès pour {$user->getEmail()} a été créé avec succès pour {$company->getName()}.");

            return $this->redirectToRoute('app_company_index');
        }

        return $this->render('admin/god/add_user.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }
}
