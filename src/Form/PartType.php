<?php

namespace App\Form;

use App\Entity\Machine;
use App\Entity\Part;
use App\Entity\Supplier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', null, [
                'label' => 'Référence constructeur',
                'attr' => ['class' => 'form-control', 'placeholder' => 'ex: SKU-9900']
            ])
            ->add('designation', null, [
                'label' => 'Nom de la pièce',
                'attr' => ['class' => 'form-control', 'placeholder' => 'ex: Courroie']
            ])
            // On peut grouper prix et stock sur la même ligne dans le Twig plus tard
            ->add('price', null, [
                'label' => 'Prix d\'achat HT',
                'attr' => ['class' => 'form-control']
            ])
            ->add('stockQuantity', null, [
                'label' => 'Quantité en stock',
                'attr' => ['class' => 'form-control']
            ])
            ->add('supplier', EntityType::class, [
                'class' => Supplier::class,
                'choice_label' => 'name',
                'label' => 'Fournisseur principal',
                'placeholder' => '--- Sélectionner un fournisseur ---',
                'attr' => ['class' => 'form-select']
            ])
            ->add('machines', EntityType::class, [
                'class' => Machine::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false, // Plus propre si la liste s'allonge
                'label' => 'Machines compatibles',
                'attr' => ['class' => 'form-select select2-enable'] // Une classe pour le JS plus tard
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Part::class,
        ]);
    }
}
