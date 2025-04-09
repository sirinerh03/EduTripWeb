<?php
namespace App\Form;

use App\Entity\Hebergement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
class HebergementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomh')
            ->add('typeh', ChoiceType::class, [
                'choices' => Hebergement::TYPE_CHOICES,
                'placeholder' => 'Choisir un type', // Add empty default option
                'required' => true,
                'attr' => [
                    'class' => 'form-select' // Optional: for styling
                ]
            ])
            ->add('adressh')
            ->add('capaciteh')
            ->add('prixh')
            ->add('disponibleh', ChoiceType::class, [
                'choices' => Hebergement::AVAILABILITY_CHOICES,
                'placeholder' => 'Choisir une disponibilité', // Add empty default option
                'required' => true,
                'attr' => [
                    'class' => 'form-select' // Optional: for styling
                ]
            ])
            ->add('descriptionh', TextareaType::class)            ->add('imageh');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hebergement::class,
        ]);
    }
}
