<?php

namespace App\Form;

use App\Entity\Meal;
use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityRepository;
use Vich\UploaderBundle\Form\Type\VichImageType;

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
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Préparation',
                'required' => false,
                'attr' => [
                    'id' => 'quill-editor',
                ],
            ])
            ->add('NbPersonne', IntegerType::class, [
                'label' => 'Nombre de personne',
                'required' => true,
            ])
            ->add('time', IntegerType::class, [
                'label' => 'Temps de préparation en minutes',
                'required' => true,
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'image_uri' => false,
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
                'expanded' => false,
                'attr' => ['class' => 'select2'],
            ])
            ->add('daily', ChoiceType::class, [
                'choices' => [
                    'Petit-déjeuner' => 'breakfast',
                    'Déjeuner' => 'lunch',
                    'Encas' => 'snack',
                    'Dîner' => 'dinner',
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'label' => 'Repas de la journée',
            ])
            ->add('ingredient_id', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('ingredient_name', TextType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'select2'],
                'label' => 'Ingrédient',
            ])
            ->add('ingredient_quantity', IntegerType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Quantité',
            ])
            ->add('ingredient_measure', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Unité de mesure',
                'choices' => [
                    'g' => 'g',
                    'ml' => 'ml',
                    'cl' => 'cl',
                    'l' => 'l',
                    'kg' => 'kg',
                    'portion' => 'portion',
                    'tranche' => 'tranche',
                    'piece' => 'piece',
                    'cas' => 'cas',
                    'cac' => 'cac',
                    'tasse' => 'tasse',
                    'verre' => 'verre',
                    'bol' => 'bol',
                ],
            ])
            ->add('ingredients_data', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('existing_ingredients_data', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('MealType', ChoiceType::class, [
                'choices' => [
                    'Entrée' => 'entree',
                    'Plat' => 'maincourse',
                    'Dessert' => 'dessert',
                    'Boisson' => 'beverages',
                ],
                'expanded' => true,
                'required' => true,
                'label' => 'Type de repas',
                'multiple' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meal::class,
        ]);
    }
}
