<?php

namespace App\Form;

use App\Entity\Participation;
use App\Entity\User;
use App\Entity\Oeuvre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Retrieve the current user from options
        $currentUser = $options['user'] ?? null;

        // Artist field: pre-selected and disabled
        $builder->add('artist', EntityType::class, [
            'class' => User::class,
            'choice_label' => function (User $user) {
                return $user->getFullName();
            },
            'label' => 'Artiste',
            'attr' => ['class' => 'form-control'],
            'disabled' => true,
        ]);

        // Email field: unmapped, pre-filled with the artist’s email, readonly by default.
        $builder->add('artistEmail', TextType::class, [
            'mapped' => false,
            'label' => "Email de l'artiste",
            'attr' => ['class' => 'form-control', 'readonly' => true],
        ]);

        // Œuvre field: only list artworks that belong to the current artist.
        $builder->add('oeuvre', EntityType::class, [
            'class' => Oeuvre::class,
            'choice_label' => 'titre',
            'label' => 'Oeuvre à inscrire',
            'attr' => ['class' => 'form-control'],
            'placeholder' => 'Sélectionnez votre œuvre',
            'query_builder' => function (\App\Repository\OeuvreRepository $or) use ($currentUser) {
                return $or->createQueryBuilder('o')
                    ->where('o.artist = :artist')
                    ->setParameter('artist', $currentUser);
            },
        ]);

        // Submit button.
        $builder->add('submit', SubmitType::class, [
            'label' => 'Soumettre ma participation',
            'attr' => ['class' => 'btn btn-primary w-100 mt-3'],
        ]);

        // Use POST_SET_DATA to pre-fill the email field from the artist's data.
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
            // Custom option for passing the current user.
            'user' => null,
        ]);
    }
}
