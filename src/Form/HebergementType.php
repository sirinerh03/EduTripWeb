<?php
namespace App\Form;

use App\Entity\Hebergement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class HebergementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nomh', null, [
            'empty_data' => '', // Ensure empty submits as "" instead of null
        ])
        ->add('typeh', ChoiceType::class, [
            'choices' => Hebergement::TYPE_CHOICES,
                'placeholder' => 'Choisir un type',
                'required' => true,
                'empty_data' => 0, 
        ])
            ->add('adressh', null, [
                'empty_data' => '', // Ensure empty submits as "" instead of null
            ])
            ->add('capaciteh', IntegerType::class, [
                'required' => true,
                'empty_data' => 0, // Default value if empty
            ])
            ->add('prixh', NumberType::class, [
                'required' => true,
                'empty_data' => 0.0, // Default value if empty
            ])
            ->add('disponibleh', ChoiceType::class, [
                'choices' => Hebergement::AVAILABILITY_CHOICES,
                'placeholder' => 'Choisir une disponibilité',
                'required' => true,
                'empty_data' => '',
 
            ])
            ->add('descriptionh', TextareaType::class, [
                'empty_data' => '',
            ])
            ->add('imageh', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hebergement::class,
            'empty_data' => null,
        ]);
    }
}