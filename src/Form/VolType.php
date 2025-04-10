<?php

namespace App\Form;

use App\Entity\Vol;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class VolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('num_vol', TextType::class, [
                'label' => 'Numéro de vol',
            ])
            ->add('aeroport_depart', TextType::class, [
                'label' => 'Aéroport de départ',
            ])
            ->add('aeroport_arrivee', TextType::class, [
                'label' => 'Aéroport d\'arrivée',
            ])
            ->add('date_depart', DateTimeType::class, [
                'label' => 'Date de départ',
                'widget' => 'single_text',
            ])
            ->add('date_arrivee', DateTimeType::class, [
                'label' => 'Date d\'arrivée',
                'widget' => 'single_text',
            ])
            ->add('places_dispo', IntegerType::class, [
                'label' => 'Places disponibles',
            ])
            ->add('prix_vol', MoneyType::class, [
                'label' => 'Prix du vol',
                'currency' => 'TND',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vol::class,
        ]);
    }
}
