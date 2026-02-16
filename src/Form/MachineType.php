<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Machine;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class MachineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('brand')
            ->add('serialNumber')
            ->add('status')
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisissez une entreprise', 'required' => false,
            ])
            // Ajout du champ image
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer l\'image actuelle',
                'download_uri' => false, // On désactive le lien de téléchargement direct
                'image_uri' => true,     // On active l'affichage de l'image existante
                'asset_helper' => true,
                'label' => 'Photo de la machine',
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Machine::class,
        ]);
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
