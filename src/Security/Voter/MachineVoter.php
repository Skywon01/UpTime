<?php

namespace App\Security\Voter;

use App\Entity\Machine;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

final class MachineVoter extends Voter
{
    public const EDIT = 'MACHINE_EDIT';
    public const VIEW = 'MACHINE_VIEW';
    public const DELETE = 'MACHINE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // On vérifie si l'attribut est géré et si l'objet est bien une Machine
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Machine;
    }

    /**
     * @param Machine $subject
     */
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null // Ajoute cet argument ici !
    ): bool {
        /** @var User $user */
        $user = $token->getUser();

        // Si l'utilisateur n'est pas connecté, accès refusé
        if (!$user instanceof UserInterface) {
            return false;
        }

        // --- CAS PARTICULIER : LE ROLE_GOD ---
        // Si tu veux que ton compte Super-Admin puisse tout voir/modifier partout
        if (in_array('ROLE_GOD', $user->getRoles())) {
            return true;
        }

        // --- LOGIQUE MULTI-ENTREPRISE ---
        // On vérifie si l'entreprise de l'utilisateur correspond à celle de la machine
        // On utilise l'ID pour une comparaison robuste
        if ($user->getCompany() === null || $subject->getCompany() === null) {
            return false;
        }

        return $user->getCompany()->getId() === $subject->getCompany()->getId();
    }
}
