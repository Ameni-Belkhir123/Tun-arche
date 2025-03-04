<?php

namespace App\Form;

use App\Entity\Publication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => 'Enter the title', 'class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'rows' => 6,
                    'class' => 'form-control',
                    'placeholder' => 'Enter the detailed description'
                ],
                'label' => 'Detailed Description'
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (optional)',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('date_act', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['placeholder' => 'YYYY-MM-DD'],
                'data' => new \DateTime(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Publication::class,
        ]);
    }
}
