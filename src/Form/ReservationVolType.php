<?php

namespace App\Form;

use App\Entity\ReservationVol;
use App\Entity\Vol;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationVolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_reservation', null, [
                'widget' => 'single_text',
            ])
            ->add('prix')
            
            ->add('nom')
            ->add('prenom')
            ->add('email')
            ->add('id_etudiant')
            ->add('nb_palce')
            ->add('vol', EntityType::class, [
                'class' => Vol::class,
                'choice_label' => 'numVol', // Ce qui s'affiche dans la liste déroulante
                'choice_value' => 'idVol',  // Ce que Symfony utilise en interne pour identifier l'objet
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationVol::class,
        ]);
    }
}
