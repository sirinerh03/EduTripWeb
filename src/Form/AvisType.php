<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\Avis;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => fn(User $u) => $u->getPrenom() . ' ' . $u->getNom(),
                'label' => 'Auteur',
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire',
            ])
            ->add('note', IntegerType::class, [
                'label' => 'Note (1–5)',
                'required' => false,
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo (jpg/png)',
                'mapped' => false,
                'required' => false,
                'attr' => ['accept' => 'image/*'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
        ]);
    }
}
