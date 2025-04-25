<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu du post',
                'attr' => ['rows' => 5]
            ])
            ->add('categorie', TextType::class, [
                'label' => 'Catégorie'
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image (JPEG, PNG)',
                'required' => false,
                'mapped' => false
            ]);
          
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'id_etudiant' => null,
        ]);
        
        $resolver->setAllowedTypes('id_etudiant', ['int', 'null']);
    }
}