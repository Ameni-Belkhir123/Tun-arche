<?php

namespace App\Form;

use App\Entity\Formation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description')
            ->add('datedebut', null, [
                'widget' => 'single_text',
            ])
            ->add('datefin', null, [
                'widget' => 'single_text',
            ])
            ->add('nbrplaces')
            ->add('link')
            ->add('imageFile' , VichImageType::class, [
                'label' => 'Image (JPEG, PNG)',
                'required' => false, // L'image n'est pas obligatoire
                'allow_delete' => true, // Permettre la suppression de l'image
                'download_uri' => false, // Ne pas afficher le lien de téléchargement
                'image_uri' => true, // Afficher l'image actuelle (si elle existe)
                'asset_helper' => true, // Utiliser Asset pour générer l'URL de l'image
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
