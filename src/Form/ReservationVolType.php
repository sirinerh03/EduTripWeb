<?php
namespace App\Form;

use App\Entity\ReservationVol;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ReservationVolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, ['label' => 'Nom'])
            ->add('prenom', null, ['label' => 'Prénom'])
            ->add('email', null, ['label' => 'Email'])
            ->add('id_etudiant', null, ['label' => 'ID Étudiant'])
            ->add('nb_palce', null, ['label' => 'Nombre de places'])
            ->add('submit', SubmitType::class, ['label' => 'Réserver']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationVol::class,
        ]);
    }
}
