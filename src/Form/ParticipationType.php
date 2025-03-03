<?php

namespace App\Form;

use App\Entity\Participation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Artiste field: pre-selected, disabled
        $builder->add('artist', EntityType::class, [
            'class' => User::class,
            'choice_label' => function (User $user) {
                return $user->getFullName();
            },
            'query_builder' => function (\App\Repository\UserRepository $ur) {
                return $ur->createQueryBuilder('u')
                    ->where('u.role = :role')
                    ->setParameter('role', 'artist');
            },
            'label' => 'Artiste',
            'attr' => ['class' => 'form-control'],
            'disabled' => true,
        ]);

        // Email field: unmapped, pre-filled with the artist’s email, readonly by default.
        $builder->add('artistEmail', TextType::class, [
            'mapped' => false,
            'label' => 'Email de l\'artiste',
            'attr' => ['class' => 'form-control', 'readonly' => true],
        ]);

        // Oeuvre field (optional)
        $builder->add('oeuvre', null, [
            'label' => 'Oeuvre (optionnel)',
            'required' => false,
            'choice_label' => 'titre',
            'attr' => ['class' => 'form-control'],
        ]);

        // Image field
        $builder->add('imageFile', FileType::class, [
            'label' => 'Image',
            'mapped' => false,
            'required' => false,
            'attr' => ['class' => 'form-control'],
        ]);

        // Submit button
        $builder->add('submit', SubmitType::class, [
            'label' => 'Soumettre ma participation',
            'attr' => ['class' => 'btn btn-primary w-100 mt-3'],
        ]);

        // Use POST_SET_DATA to pre-fill the email field.
        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            $email = '';
            if ($data && $data->getArtist() && method_exists($data->getArtist(), 'getEmail')) {
                $email = $data->getArtist()->getEmail();
            }
            $form->get('artistEmail')->setData($email);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participation::class,
        ]);
    }
}
