<?php

namespace App\Form;

use App\Entity\Candidature;
use App\Entity\University;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CandidatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    
    {
        $candidature = $options['data'];
        $isEdit = $candidature && $candidature->getId() !== null;

        $builder
            ->add('university', EntityType::class, [//choicelabel
                'class' => University::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choose a university',
                'required' => true,
                'label_attr' => ['class' => 'form-label fw-bold'],
                'attr' => ['class' => 'form-select']
            ])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'en_attente',
                    'Acceptée' => 'acceptee',
                    'Refusée' => 'refusee'
                ],
                'placeholder' => 'Choose status',
                'required' => true,
                'data' => $candidature->getEtat() ?: 'en_attente', // Set default if 'etat' is null
                'label_attr' => ['class' => 'form-label fw-bold'],
                'attr' => ['class' => 'form-select']
            ])
            ->add('cv', FileType::class, [
                'label' => 'CV (PDF file)',
                'mapped' => false,
                'required' => !$isEdit,
                'help' => $isEdit && $candidature->getCv() ? 'Current file: ' . $candidature->getCv() : null,
                'help_html' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['application/pdf', 'application/x-pdf'],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
                'attr' => ['placeholder' => $isEdit ? 'Leave empty to keep current file' : 'Choose a PDF file'],
                'label_attr' => ['class' => 'form-label fw-bold']
            ])
            ->add('lettre_motivation', FileType::class, [
                'label' => 'Lettre de motivation (PDF file)',
                'mapped' => false,
                'required' => !$isEdit,
                'help' => $isEdit && $candidature->getLettreMotivation() ? 'Current file: ' . $candidature->getLettreMotivation() : null,
                'help_html' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['application/pdf', 'application/x-pdf'],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
                'attr' => ['placeholder' => $isEdit ? 'Leave empty to keep current file' : 'Choose a PDF file'],
                'label_attr' => ['class' => 'form-label fw-bold']
            ])
            ->add('diplome', FileType::class, [
                'label' => 'Diplôme (PDF file)',
                'mapped' => false,
                'required' => !$isEdit,
                'help' => $isEdit && $candidature->getDiplome() ? 'Current file: ' . $candidature->getDiplome() : null,
                'help_html' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['application/pdf', 'application/x-pdf'],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
                'attr' => ['placeholder' => $isEdit ? 'Leave empty to keep current file' : 'Choose a PDF file'],
                'label_attr' => ['class' => 'form-label fw-bold']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidature::class,
        ]);
    }
}
