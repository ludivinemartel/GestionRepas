<?php

namespace App\Form;

use App\Entity\UserPreference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPreferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('palette', ChoiceType::class, [
                'choices' => [
                    'Palette Succulents' => 'palettes-1',
                    'Palette Purple Sky' => 'palettes-2',
                    'Palette Lucky Charms' => 'palettes-3',
                    'Palette Summer Sea' => 'palettes-4',
                    'Palette Preppy' => 'palettes-5',
                ],
                'label' => 'Choix de la palette',
                'expanded' => true,
                'multiple' => false,
                'choice_label' => function ($choice, $key, $value) {
                    return '<div class="palette-preview palette-' . $value . '">
                                <span class="palette-color" style="background-color: var(--neutral-color);"></span>
                                <span class="palette-color" style="background-color: var(--primary-color);"></span>
                                <span class="palette-color" style="background-color: var(--secondary-color);"></span>
                                <span class="palette-color" style="background-color: var(--complementary-color);"></span>
                                <span class="palette-color" style="background-color: var(--bold-color);"></span>
                                ' . $key . '
                            </div>';
                },
                'choice_attr' => function ($choice, $key, $value) {
                    return ['class' => 'palette-option', 'data-html' => true];
                },
                'attr' => [
                    'class' => 'palette-choices'
                ],
            ])
            ->add('numberOfAdults', IntegerType::class, [
                'label' => 'Nombre d\'adultes',
                'required' => false,
            ])
            ->add('numberOfChildren', IntegerType::class, [
                'label' => 'Nombre d\'enfants',
                'required' => false,
            ])
            ->add('diet', ChoiceType::class, [
                'choices' => [
                    'Omnivore' => 'omnivore',
                    'Végétarien' => 'vegetarian',
                    'Végan' => 'vegan',
                    'Autre' => 'other',
                ],
                'label' => 'Régime alimentaire',
                'required' => false,
            ])
            ->add('stockCategories', ChoiceType::class, [
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
                ],
                'label' => 'Catégories de stock',
                'expanded' => true,
                'multiple' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserPreference::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
