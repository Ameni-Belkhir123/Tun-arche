<?php

namespace App\Form;

use App\Entity\Oeuvre;
use App\Entity\Galerie;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OeuvreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr'  => ['class' => 'form-control', 'placeholder' => 'Enter titre']
            ])
            ->add('galerie', EntityType::class, [
                'class' => Galerie::class,
                'choice_label' => 'nom',
                'label' => 'Galerie',
                'attr'  => ['class' => 'form-control']
            ])
            ->add('artist', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return $user->getFullName();
                },
                'label' => 'Artist',
                'query_builder' => function(UserRepository $ur) {
                    return $ur->createQueryBuilder('u')
                        ->where('u.role = :role')
                        ->setParameter('role', 'artist');
                },
                'attr'  => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr'  => ['class' => 'form-control', 'placeholder' => 'Enter description'],
                'required' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (optional)',
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Oeuvre::class,
        ]);
    }
}
