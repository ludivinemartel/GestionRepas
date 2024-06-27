<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Service\UserFiltreService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieFilterType extends AbstractType
{
    private $userFiltreService;

    public function __construct(UserFiltreService $userFiltreService)
    {
        $this->userFiltreService = $userFiltreService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'name',
                'placeholder' => 'Toutes les catégories',
                'required' => false,
                'query_builder' => function () {
                    return $this->userFiltreService->getQueryBuilderForCurrentUser(Categorie::class);
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
