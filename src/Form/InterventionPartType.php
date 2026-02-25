<?php

namespace App\Form;

use App\Entity\Intervention;
use App\Entity\InterventionConsumedPart;
use App\Entity\Part;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionPartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $machine = $options['machine'];

        $builder
            ->add('part', EntityType::class, [
                'class' => Part::class,
                'choice_label' => 'designation',
                'query_builder' => function (EntityRepository $er) use ($machine) {
                    $qb = $er->createQueryBuilder('p');

                    if ($machine) {
                        // On filtre les pièces qui ont cette machine dans leur collection
                        $qb->join('p.machines', 'm')
                            ->where('m.id = :machineId')
                            ->setParameter('machineId', $machine->getId());
                    }

                    return $qb->orderBy('p.designation', 'ASC');
                },
                'label' => 'Pièce compatible',
                'attr' => ['class' => 'form-select']
            ])
            ->add('quantity', null, [
                'label' => 'Quantité',
                'attr' => ['min' => 1, 'class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InterventionConsumedPart::class,
            'machine' => null,
        ]);
    }
}
