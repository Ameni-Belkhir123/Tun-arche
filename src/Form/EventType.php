<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name_event')
            ->add('date_start', null, ['widget' => 'single_text'])
            ->add('date_end', null, ['widget' => 'single_text'])
            ->add('place_event')
            ->add('discription', TextareaType::class, ['attr' => ['placeholder' => 'Enter event description', 'rows' => 5]])
            ->add('price')
            ->add('totalTickets', IntegerType::class, ['label' => 'Total Tickets']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Event::class]);
    }
}
