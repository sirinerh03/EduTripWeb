<?php

namespace App\Form;

use App\Entity\Vol;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class VolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('num_vol', TextType::class, [
                'label' => 'Numéro de vol',
                'attr' => [
                    'maxlength' => 7,
                    'placeholder' => 'Ex: AB123$!',
                ]
            ])
            ->add('aeroport_depart', TextType::class, [
                'label' => 'Aéroport de départ',
                'attr' => [
                    'placeholder' => 'Ex: Tunis-Carthage'
                ]
            ])
            ->add('aeroport_arrivee', TextType::class, [
                'label' => 'Aéroport d\'arrivée',
                'attr' => [
                    'placeholder' => 'Ex: Paris-CDG'
                ]
            ])
            ->add('date_depart', DateTimeType::class, [
                'label' => 'Date de départ',
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('date_arrivee', DateTimeType::class, [
                'label' => 'Date d\'arrivée',
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('places_dispo', IntegerType::class, [
                'label' => 'Places disponibles',
                'attr' => [
                    'min' => 0,
                    'placeholder' => 'Ex: 100'
                ]
            ])
            ->add('prix_vol', NumberType::class, [
                'label' => 'Prix du vol (TND)',
                'scale' => 3,
                'attr' => [
                    'step' => '0.001',
                    'min' => 0,
                    'placeholder' => 'Ex: 299.999'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vol::class,
        ]);
    }
}
