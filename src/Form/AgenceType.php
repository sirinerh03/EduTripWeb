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
use Symfony\Component\Validator\Constraints\NotBlank;

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
            ->add('telephone_ag', TelType::class, [
                'label' => 'Téléphone*',
                'attr' => [
                    'placeholder' => '12345678',
                    'class' => 'form-control'
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
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date de création est obligatoire.'
                    ])
                ],
                'empty_data' => (new \DateTime())->format('Y-m-d'),  // Définit la date actuelle si vide
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agence::class,
        ]);
    }
}
