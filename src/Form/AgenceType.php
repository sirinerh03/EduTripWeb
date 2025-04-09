<?php
namespace App\Form;

use App\Entity\Agence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TelType;

class AgenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_ag', TextType::class, [
                'label' => 'Nom de l\'agence*',
                'attr' => [
                    'placeholder' => 'Entrez le nom de l\'agence',
                    'class' => 'form-control'
                ]
            ])
            ->add('adresse_ag', TextType::class, [
                'label' => 'Adresse*',
                'attr' => [
                    'placeholder' => 'Entrez l\'adresse complète',
                    'class' => 'form-control'
                ]
            ])
            ->add('telephoneAg', TelType::class, [
                'label' => 'Téléphone*',
                'attr' => [
                    'placeholder' => '12345678',
                    'class' => 'form-control',
                    'pattern' => '[0-9]{8}',
                    'title' => '8 chiffres requis'
                ]
            ])
            ->add('email_ag', EmailType::class, [
                'label' => 'Email*',
                'attr' => [
                    'placeholder' => 'email@exemple.com',
                    'class' => 'form-control'
                ]
            ])
            ->add('description_ag', TextareaType::class, [
                'label' => 'Description*',
                'attr' => [
                    'rows' => 4,
                    'class' => 'form-control',
                    'placeholder' => 'Décrivez votre agence...'
                ]
            ])
            ->add('date_creation', DateType::class, [
                'label' => 'Date de création*',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'max' => date('Y-m-d')
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agence::class,
        ]);
    }
}