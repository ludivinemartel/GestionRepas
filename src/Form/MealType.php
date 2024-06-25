<?php

namespace App\Form;

use App\Entity\Meal;
use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;
use App\Form\IngredientType;
use Doctrine\ORM\EntityRepository;

class MealType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('ingredients', CollectionType::class, [
                'entry_type' => IngredientType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__name__',
                'attr' => [
                    'class' => 'ingredients',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Preparation'
            ])
            ->add('NbPersonne', IntegerType::class, [
                'label' => 'Nombre de personne'
            ])
            ->add('time', IntegerType::class, [
                'label' => 'Temps de préparation en minutes'
            ])
            ->add('kcal', IntegerType::class, [
                'label' => 'Calories pour 100 g'
            ])
            ->add('glucide', IntegerType::class, [
                'label' => 'Glucides'
            ])
            ->add('proteine', IntegerType::class, [
                'label' => 'Protéines'
            ])
            ->add('lipide', IntegerType::class, [
                'label' => 'Lipides'
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image (JPEG, PNG)',
                'required' => false,
            ])
            ->add('categories', EntityType::class, [
                'class' => Categorie::class,
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('c')
                        ->andWhere('c.user = :user')
                        ->setParameter('user', $user);
                },
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Catégories'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meal::class,
        ]);
    }
}
