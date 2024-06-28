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
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantité'
            ])
            ->add('measure', ChoiceType::class, [
                'label' => 'Unité de mesure',
                'choices' => [
                    '' => '',
                    'ml' => 'ml',
                    'cl' => 'cl',
                    'dl' => 'dl',
                    'l' => 'l',
                    'g' => 'g',
                    'kg' => 'kg',
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
