<?php

namespace App\Form;

use App\Entity\Billet;
use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BilletType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mode_paiement', ChoiceType::class, [
                'choices' => [
                    'Carte de Crédit' => 'credit_card',
                    'PayPal' => 'paypal',
                    'Virement Bancaire' => 'bank_transfer',
                    'Chèque' => 'check',
                ],
                'placeholder' => 'Select Payment Method',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Billet Standard' => 'standard',
                    'Billet Premium' => 'premium',
                    'Billet VIP' => 'vip',
                ],
                'placeholder' => 'Select Ticket Type',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'nameEvent',
                'placeholder' => 'Select an event',
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Billet::class,
        ]);
    }
}
