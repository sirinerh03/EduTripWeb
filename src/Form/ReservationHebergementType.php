<?php
namespace App\Form;

use App\Entity\Hebergement;
use App\Entity\ReservationHebergement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationHebergementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_d', null, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'], // Optional: for styling
                'label' => 'Date de début',
                'help' => 'Sélectionnez une date à partir d\'aujourd\'hui.',
            ])
            ->add('date_f', null, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'], // Optional: for styling
                'label' => 'Date de fin',
                'help' => 'Sélectionnez une date après la date de début.',
            ])
            ->add('status', TextType::class, [
                'attr' => ['class' => 'form-control'], // Optional: for styling
                'label' => 'Statut',
                'help' => 'Indiquez le statut de la réservation.',
            ])
            ->add('hebergement', EntityType::class, [
                'class' => Hebergement::class,
                'choice_label' => 'nomh', // Display the name of the accommodation
                'label' => 'Hébergement',
                'attr' => ['class' => 'form-select'], // Optional: for styling
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationHebergement::class,
        ]);
    }
}
