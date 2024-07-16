<?php

namespace App\Form;

use App\Entity\WeeklyMeal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WeeklyMealType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('dateRange', TextType::class, [
                'required' => true,
                'label' => 'Date Range',
                'attr' => [
                    'class' => 'form-control datepicker',
                    'placeholder' => 'Sélectionner dates'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WeeklyMeal::class,
        ]);
    }
}
