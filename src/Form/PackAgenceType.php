<?php

namespace App\Form;

use App\Entity\Pack_agence;
use App\Entity\Agence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PackAgenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('id_agence', EntityType::class, [
            'class' => Agence::class,
            'choice_label' => 'nom_ag',
            'label' => 'Agence',
        ])
        
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'step'  => '0.01',  // This helps browsers show appropriate input options for floats.
                ],
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en mois)',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('services_inclus', TextType::class, [
                'label' => 'Services inclus',
            ])
            
            ->add('date_ajout', DateType::class, [
                'label' => 'Date d\'ajout',
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Actif' => 'actif',
                    'Inactif' => 'inactif',
                ],
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('nom_pk', TextType::class, [
                'label' => 'Nom du Pack',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description_pk', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pack_agence::class,
        ]);
    }
}
