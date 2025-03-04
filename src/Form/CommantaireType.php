<?php
// File: src/Form/CommantaireType.php

namespace App\Form;

use App\Entity\Commantaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommantaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Only the comment content is needed since the publication and user are set automatically.
        $builder
            ->add('contenu', TextType::class, [
                'empty_data' => '',
                'attr' => ['placeholder' => 'Enter your comment here', 'class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commantaire::class,
        ]);
    }
}
