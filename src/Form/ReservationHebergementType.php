<?php

namespace App\Form;

use App\Entity\ReservationHebergement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReservationHebergementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_d', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->format('Y-m-d')
                ],
                'label' => 'Date de début',
                'empty_data' => null,
                'required' => true,
            ])
            ->add('date_f', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->modify('+1 day')->format('Y-m-d')
                ],
                'label' => 'Date de fin',
                'empty_data' => null,
                'required' => true,
            ])
            ->add('status', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Votre commentaire...'
                ],
                'label' => 'Commentaire',
                'required' => true,
            ]);

        if ($options['is_new']) {
            $builder->add('hebergement', TextType::class, [
                'mapped' => false,
                'data' => $options['hebergement_name'],
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
                ],
                'label' => 'Hébergement'
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationHebergement::class,
            'hebergement_name' => null,
            'is_new' => false,
        ]);
    }
}