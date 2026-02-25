<?php

namespace App\Form;

use App\Entity\Intervention;
use App\Entity\Machine;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class InterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $intervention = $options['data'] ?? null;
        $machine = $intervention ? $intervention->getMachine() : null;

        $builder
            ->add('title', null, [
                'label' => 'Objet de l\'intervention',
                'attr' => ['placeholder' => 'ex: Remplacement contacteur'],
            ])
            ->add('description', null, [
                'label' => 'Détails de l\'opération',
                'attr' => ['rows' => 4]
            ])
            ->add('createdAt', null, [

                'widget' => 'single_text',
                'label' => 'Date d\'intervention',
            ])
            ->add('price', null, [
                'label' => 'Coût HT (€)',
            ])
            ->add('machine', EntityType::class, [
                'class' => Machine::class,
                'choice_label' => 'name',
                'label' => 'Machine',
            ])
            ->add('technician', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Technicien',
            ])
            ->add('interventionConsumedParts', CollectionType::class, [
                'entry_type' => InterventionPartType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,      // Crucial pour le JS
                'allow_delete' => true,   // Crucial pour le JS
                'by_reference' => false,  // Oblige Symfony à appeler addInterventionConsumedPart()
                'constraints' => [
                    new Valid(), // Valide les sous-forms
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
        ]);
    }
}
