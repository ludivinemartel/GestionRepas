<?php

namespace App\Form;

use App\Entity\WeeklyMeal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WeeklyMealType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weekStart', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Début de la semaine',
                'required' => true,
            ])
            ->add('weekEnd', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fin de la semaine',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WeeklyMeal::class,
        ]);
    }
}
