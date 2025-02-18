<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => ['placeholder' => 'Enter Name'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'The name is required.']),
                    new Assert\Length(['min' => 2, 'max' => 50, 'minMessage' => 'The name must have at least 2 characters.'])
                ]
            ])
       
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'attr' => ['placeholder' => 'Enter Last Name'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'The last name is required.']),
                    new Assert\Length(['min' => 2, 'max' => 50, 'minMessage' => 'The last name must have at least 2 characters.'])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Enter Email'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'The email is required.']),
                    new Assert\Email(['message' => 'The email is not valid.'])
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'attr' => ['placeholder' => 'Enter Password'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'The password is required.']),
                    new Assert\Length(['min' => 6, 'minMessage' => 'The password must be at least 6 characters long.'])
                ]
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Role',
                'choices' => [
                    'User' => 'user',
                    'Admin' => 'admin', 
                    'Artist' => 'artist',
                ],
                'attr' => ['placeholder' => 'Select Role'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'The role is required.']),
                    new Assert\Choice([
                        'choices' => ['user', 'admin', 'artist'],
                        'message' => 'Please select a valid role.'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
         
        ]);
    }
}
