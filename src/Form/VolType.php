<?php

namespace App\Form;

use App\Entity\Vol;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class VolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('num_vol', TextType::class, [
                'label' => 'Numéro du vol',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le numéro de vol est requis.'
                    ])
                ]
            ])
            ->add('aeroport_depart', TextType::class, [
                'required' => true,
                'label' => 'Aéroport de départ',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Aéroport de départ requis.'
                    ])
                ]
            ])
            ->add('aeroport_arrivee', TextType::class, [
                'label' => 'Aéroport d\'arrivée',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Aéroport d’arrivée requis.'
                    ])
                ]
            ])
            ->add('date_depart', DateTimeType::class, [
                'label' => 'Date de départ',
                'widget' => 'single_text'
            ])
            ->add('date_arrivee', DateTimeType::class, [
                'label' => 'Date d\'arrivée',
                'widget' => 'single_text'
            ])
            ->add('places_dispo', IntegerType::class, [
                'label' => 'Places disponibles',
                'attr' => [
                    'min' => 0,
                    'placeholder' => 'Ex: 100'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Places disponibles requises.'
                    ]),
                    new Positive([
                        'message' => 'Le nombre de places doit être positif.'
                    ])
                ]
            ])
            ->add('prix_vol', NumberType::class, [
                'label' => 'Prix du vol',
                'attr' => [
                    'placeholder' => 'Ex: 150.00'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le prix est requis.'
                    ]),
                    new Positive([
                        'message' => 'Le prix doit être un nombre positif.'
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => ['class' => 'btn btn-success'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vol::class,
        ]);
    }
}
