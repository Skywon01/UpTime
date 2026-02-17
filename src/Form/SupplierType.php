<?php

namespace App\Form;

use App\Entity\Supplier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom de l\'entreprise',
                'attr' => ['placeholder' => 'ex: Roulements & Co']
            ])
            ->add('contactName', null, ['label' => 'Nom du contact'])
            ->add('email', null, ['attr' => ['placeholder' => 'contact@fournisseur.com']])
            ->add('phoneNumber', null, ['label' => 'Téléphone'])
            ->add('website', null, ['label' => 'Site Web'])
            ->add('notes', null, [
                'label' => 'Notes internes',
                'attr' => ['rows' => 3]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Supplier::class,
        ]);
    }
}
