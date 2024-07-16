<?php

namespace App\Form;

use App\Entity\PantryItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PantryItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom'
            ])

            ->add('InStock', ChoiceType::class, [
                'label' => 'En stock',
                'choices'  => [
                    'En stock' => true,
                    'A acheter' => false,
                    'Neutre' => null,
                ],
                'required' => false,
                'placeholder' => 'Selectionner statut',
            ])

            ->add('type', ChoiceType::class, [
                'label' => 'Type',

                'choices' => [
                    'Fruits' => 'Fruits',
                    'Légumes' => 'Légumes',
                    'Produits Laitiers' => 'Produits Laitiers',
                    'Viande' => 'Viande',
                    'Poisson' => 'Poisson',
                    'Surgelé' => 'Surgelé',
                    'Sauces' => 'Sauces',
                    'Féculents' => 'Féculents',
                    'Oléagineux' => 'Oléagineux',
                    'Conserve' => 'Conserve',
                    'Herbes' => 'Herbes',
                    'Légumineuses' => 'Légumineuses',
                    'Épicerie sèche' => 'Épicerie sèche',
                    'Epices' => 'Epices',
                    'Hygiène' => 'Hygiène',
                    'Enfant' => 'Enfant',
                    'Fournitures ménagères' => 'Fournitures ménagères',
                    'Médicaments' => 'Médicaments',
                    'Papeterie' => 'Papeterie',
                    'Animaux' => 'Animaux',
                    'Divers' => 'Divers',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PantryItem::class,
        ]);
    }
}
