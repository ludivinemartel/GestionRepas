<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Entity\Ingredient;
use App\Entity\FoodComposition;
use App\Form\MealType;
use App\Form\CategorieFilterType;
use App\Repository\MealRepository;
use App\Service\UnitConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/meal')]
class MealController extends AbstractController
{


    #[Route('/', name: 'app_meal_index', methods: ['GET', 'POST'])]
    public function index(Request $request, MealRepository $mealRepository, Security $security, PaginatorInterface $paginator): Response
    {
        $user = $security->getUser();
        $form = $this->createForm(CategorieFilterType::class);
        $form->handleRequest($request);

        $type = $request->query->get('type');
        $mealsQuery = $mealRepository->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->setParameter('user', $user);

        if ($form->isSubmitted() && $form->isValid()) {
            $categorie = $form->get('categorie')->getData();

            if ($categorie) {
                $mealsQuery
                    ->innerJoin('m.Categories', 'c')
                    ->andWhere('c.id = :category')
                    ->setParameter('category', $categorie->getId());
            }
        }

        if ($type) {
            $mealsQuery->andWhere('m.Daily LIKE :type')->setParameter('type', '%' . $type . '%');
        }

        $pagination = $paginator->paginate(
            $mealsQuery->getQuery(), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            6 /*limit per page*/
        );

        return $this->render('meal/index.html.twig', [
            'form' => $form->createView(),
            'pagination' => $pagination,
            'type' => $type,
        ]);
    }

    #[Route('/types', name: 'app_meal_types', methods: ['GET'])]
    public function types(): Response
    {
        return $this->render('meal/types.html.twig');
    }

    #[Route('/new', name: 'app_meal_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security, UnitConverter $unitConverter): Response
    {
        $meal = new Meal();
        $form = $this->createForm(MealType::class, $meal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            if ($user) {
                $meal->setUser($user);
            } else {
                throw new \Exception('User must be logged in to create a meal.');
            }

            $ingredientDataJson = $form->get('ingredients_data')->getData();
            $ingredientData = json_decode($ingredientDataJson, true);

            $totalKcal = 0;
            $totalLipide = 0;
            $totalGlucide = 0;
            $totalProteine = 0;

            if (is_array($ingredientData)) {
                foreach ($ingredientData as $ingredientItem) {
                    $ingredient = new Ingredient();
                    $ingredient->setName($ingredientItem['name']);
                    $ingredient->setQuantity($ingredientItem['quantity']);
                    $ingredient->setMesure($ingredientItem['measure']);

                    $foodComposition = $entityManager->getRepository(FoodComposition::class)->find($ingredientItem['id']);
                    if ($foodComposition) {
                        $ingredient->setFoodCompositionId($foodComposition);

                        $quantityInGrams = $unitConverter->toGrams($ingredientItem['quantity'], $ingredientItem['measure']);

                        $totalKcal += ($foodComposition->getKcal() ?? 0) * ($quantityInGrams / 100);
                        $totalLipide += ($foodComposition->getFat() ?? 0) * ($quantityInGrams / 100);
                        $totalGlucide += ($foodComposition->getCarbohydrate() ?? 0) * ($quantityInGrams / 100);
                        $totalProteine += ($foodComposition->getProtein() ?? 0) * ($quantityInGrams / 100);
                    } else {
                        throw new \Exception('Invalid FoodComposition ID.');
                    }

                    $meal->addIngredient($ingredient);
                }
            } else {
                $this->addFlash('error', 'Invalid ingredient data.');
                return $this->render('meal/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $meal->setKcal($totalKcal);
            $meal->setLipide($totalLipide);
            $meal->setGlucide($totalGlucide);
            $meal->setProteine($totalProteine);

            $entityManager->persist($meal);
            $entityManager->flush();

            $this->addFlash('success', 'Meal created successfully with ingredients.');

            return $this->redirectToRoute('app_meal_types');
        }

        return $this->render('meal/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_meal_show', methods: ['GET'])]
    public function show(Meal $meal, Request $request): Response
    {
        $type = $request->query->get('type');

        return $this->render('meal/show.html.twig', [
            'meal' => $meal,
            'type' => $type,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_meal_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Meal $meal, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MealType::class, $meal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle deleted ingredients
            $deletedIngredients = $request->request->get('deleted_ingredients');
            if ($deletedIngredients) {
                $deletedIngredients = json_decode($deletedIngredients, true);
                foreach ($deletedIngredients as $ingredientId) {
                    $ingredient = $entityManager->getRepository(Ingredient::class)->find($ingredientId);
                    if ($ingredient) {
                        $meal->removeIngredient($ingredient);
                        $entityManager->remove($ingredient);
                    }
                }
            }

            // Handle new/updated ingredients
            $ingredientDataJson = $form->get('ingredients_data')->getData();
            $ingredientData = json_decode($ingredientDataJson, true);

            if (is_array($ingredientData)) {
                foreach ($ingredientData as $ingredientItem) {
                    if (isset($ingredientItem['id']) && $ingredientItem['id']) {
                        // Update existing ingredient
                        $ingredient = $entityManager->getRepository(Ingredient::class)->find($ingredientItem['id']);
                        if ($ingredient) {
                            $ingredient->setName($ingredientItem['name']);
                            $ingredient->setQuantity($ingredientItem['quantity']);
                            $ingredient->setMesure($ingredientItem['measure']);
                        }
                    } else {
                        // Add new ingredient
                        $ingredient = new Ingredient();
                        $ingredient->setName($ingredientItem['name']);
                        $ingredient->setQuantity($ingredientItem['quantity']);
                        $ingredient->setMesure($ingredientItem['measure']);
                        $ingredient->setMeal($meal); // Set the meal
                        if (isset($ingredientItem['food_composition_id_id'])) {
                            $foodComposition = $entityManager->getRepository(FoodComposition::class)->find($ingredientItem['food_composition_id_id']);
                            if ($foodComposition) {
                                $ingredient->setFoodCompositionId($foodComposition);
                            }
                        }
                        $meal->addIngredient($ingredient);
                        $entityManager->persist($ingredient);
                    }
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_meal_index', [], Response::HTTP_SEE_OTHER);
        }

        // Pass the existing ingredients to the view
        $ingredients = $meal->getIngredients()->map(function ($ingredient) {
            return [
                'id' => $ingredient->getId(),
                'name' => $ingredient->getName(),
                'quantity' => $ingredient->getQuantity(),
                'measure' => $ingredient->getMesure(),
                'food_composition_id_id' => $ingredient->getFoodCompositionId() ? $ingredient->getFoodCompositionId()->getId() : null,
            ];
        })->toArray();

        return $this->render('meal/edit.html.twig', [
            'meal' => $meal,
            'form' => $form->createView(),
            'ingredients' => json_encode($ingredients),
            'description' => $meal->getDescription(),
        ]);
    }



    #[Route('/{id}', name: 'app_meal_delete', methods: ['POST'])]
    public function delete(Request $request, Meal $meal, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $meal->getId(), $request->request->get('_token'))) {
            $entityManager->remove($meal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_meal_index', [], Response::HTTP_SEE_OTHER);
    }
}
