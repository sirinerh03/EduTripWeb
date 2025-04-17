<?php
namespace App\Form;

use App\Entity\ReservationVol;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReservationVolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('nom', TextType::class, [
            'label' => 'Nom',
            'attr' => [
                'placeholder' => 'Votre nom',
                'pattern' => '[A-Za-zÀ-ÿ\s\-]{3,50}',
                'title' => '2-50 caractères alphabétiques'
            ]
        ])
        ->add('prenom', TextType::class, [
            'label' => 'Prénom',
            'attr' => ['placeholder' => 'Votre prénom']
        ])
        ->add('email', EmailType::class, [
            'label' => 'Email',
            'attr' => [
                'placeholder' => 'exemple@domain.com',
                'pattern' => '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$'
            ]
        ])
        ->add('nb_place', IntegerType::class, [
            'label' => 'Nombre de places',
            'attr' => [
                'min' => 1,
                'max' => 10
            ]
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Réserver',
            'attr' => ['class' => 'btn-reserver']
        ]);
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationVol::class,
        ]);
    }
}
