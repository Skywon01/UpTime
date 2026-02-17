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
                'attr' => ['placeholder' => 'ex: SKU-9900']
            ])
            ->add('designation', null, [
                'label' => 'Nom de la pièce',
                'attr' => ['placeholder' => 'ex: Courroie de transmission']
            ])
            ->add('price', null, [
                'label' => 'Prix d\'achat HT',
                'help' => 'Prix unitaire'
            ])
            ->add('stockQuantity', null, [
                'label' => 'Quantité en stock'
            ])
            ->add('supplier', EntityType::class, [
                'class' => \App\Entity\Supplier::class,
                'choice_label' => 'name',
                'label' => 'Fournisseur'
            ])
            ->add('machines', EntityType::class, [
                'class' => \App\Entity\Machine::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true, // Liste déroulante multi-sélection (plus compacte)
                'label' => 'Machines compatibles'
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
