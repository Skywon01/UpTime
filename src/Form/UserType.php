<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => ['placeholder' => 'ex: admin@client.com']
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe provisoire',
                'mapped' => true, // On le garde mappé pour le récupérer dans le contrôleur
                'constraints' => [
                    new NotBlank(message : 'Veuillez entrer un mot de passe'),
                    new Length(
                        min: 8, // Argument nommé (pas de tableau ici)
                        minMessage: 'Le mot de passe doit faire au moins {{ limit }} caractères',
                    ),
                ],
            ])
            // On ne rajoute PAS le champ 'roles' ni 'company' ici
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
