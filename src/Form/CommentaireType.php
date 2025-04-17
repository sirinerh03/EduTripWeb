<?php

namespace App\Form;

use App\Entity\Commentaire;
use App\Entity\Post;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('date_commentaire', null, [
            'widget' => 'single_text',
        ])
        ->add('contenu', TextType::class, [
            'required' => false, // désactive le required HTML5
            'empty_data' => '', // ← cette ligne est importante pour éviter le null
            'constraints' => [
                new NotBlank([
                    'message' => 'Le contenu ne peut pas être vide.',
                ]),
            ],
            'label' => 'Contenu',
            ])
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'id_etudiant',
            ])
            ->add('post', EntityType::class, [
                'class' => Post::class,
                'choice_label' => 'id_Post',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
}
