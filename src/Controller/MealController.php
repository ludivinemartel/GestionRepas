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
use Symfony\Component\String\Slugger\SluggerInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

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
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security, UnitConverter $unitConverter, SluggerInterface $slugger, UploadHandler $uploadHandler): Response
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
                        $ingredient->setFoodComposition($foodComposition);

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
            'description' => '',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_meal_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Meal $meal, EntityManagerInterface $entityManager, UnitConverter $unitConverter): Response
    {
        $form = $this->createForm(MealType::class, $meal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->get('delete_image')) {
                // Supprimer l'image
                $meal->setImageName(null);
                $meal->setImageFile(null);
            }

            // Traitement des ingrédients
            $ingredientDataJson = $form->get('ingredients_data')->getData();
            $ingredientData = json_decode($ingredientDataJson, true);

            // Mise à jour des ingrédients existants et ajout des nouveaux ingrédients
            $updatedIngredients = [];
            foreach ($ingredientData as $ingredientItem) {
                $ingredient = $entityManager->getRepository(Ingredient::class)->find($ingredientItem['id']);
                if (!$ingredient) {
                    $ingredient = new Ingredient();
                    $ingredient->setName($ingredientItem['name']);
                }
                $ingredient->setQuantity($ingredientItem['quantity']);
                $ingredient->setMesure($ingredientItem['measure']);

                $meal->addIngredient($ingredient);
                $updatedIngredients[] = $ingredient;
            }

            // Supprimer les ingrédients non présents dans les données mises à jour
            $currentIngredients = $meal->getIngredients();
            foreach ($currentIngredients as $currentIngredient) {
                if (!in_array($currentIngredient, $updatedIngredients, true)) {
                    $meal->removeIngredient($currentIngredient);
                    $entityManager->remove($currentIngredient);
                }
            }

            $entityManager->flush();

            // Recalculer les valeurs nutritionnelles après avoir mis à jour les ingrédients
            $this->recalculateNutritionValues($meal, $unitConverter, $entityManager);

            return $this->redirectToRoute('app_meal_index');
        }

        // Convertir les ingrédients existants en JSON pour préremplir le formulaire
        $existingIngredientsData = [];
        foreach ($meal->getIngredients() as $ingredient) {
            $existingIngredientsData[] = [
                'id' => $ingredient->getId(),
                'name' => $ingredient->getName(),
                'quantity' => $ingredient->getQuantity(),
                'measure' => $ingredient->getMesure(),
            ];
        }

        return $this->render('meal/edit.html.twig', [
            'form' => $form->createView(),
            'meal' => $meal,
            'existingIngredients' => json_encode($existingIngredientsData),
            'description' => $meal->getDescription(),
        ]);
    }

    private function recalculateNutritionValues(Meal $meal, UnitConverter $unitConverter, EntityManagerInterface $entityManager): void
    {
        $totalKcal = 0;
        $totalLipide = 0;
        $totalGlucide = 0;
        $totalProteine = 0;

        foreach ($meal->getIngredients() as $ingredient) {
            $foodComposition = $ingredient->getFoodComposition();
            if ($foodComposition) {
                $quantityInGrams = $unitConverter->toGrams($ingredient->getQuantity(), $ingredient->getMesure());

                $totalKcal += ($foodComposition->getKcal() ?? 0) * ($quantityInGrams / 100);
                $totalLipide += ($foodComposition->getFat() ?? 0) * ($quantityInGrams / 100);
                $totalGlucide += ($foodComposition->getCarbohydrate() ?? 0) * ($quantityInGrams / 100);
                $totalProteine += ($foodComposition->getProtein() ?? 0) * ($quantityInGrams / 100);
            }
        }

        // Mettre à jour les valeurs nutritionnelles du repas
        $meal->setKcal($totalKcal);
        $meal->setLipide($totalLipide);
        $meal->setGlucide($totalGlucide);
        $meal->setProteine($totalProteine);

        $entityManager->flush();
    }

    #[Route('/{id}', name: 'app_meal_show', methods: ['GET'])]
    public function show(Meal $meal, Request $request, UnitConverter $unitConverter): Response
    {
        $type = $request->query->get('type');

        $totalKcal = 0;
        $totalLipide = 0;
        $totalGlucide = 0;
        $totalProteine = 0;

        foreach ($meal->getIngredients() as $ingredient) {
            $foodComposition = $ingredient->getFoodComposition();
            if ($foodComposition) {
                $quantityInGrams = $unitConverter->toGrams($ingredient->getQuantity(), $ingredient->getMesure());

                $totalKcal += ($foodComposition->getKcal() ?? 0) * ($quantityInGrams / 100);
                $totalLipide += ($foodComposition->getFat() ?? 0) * ($quantityInGrams / 100);
                $totalGlucide += ($foodComposition->getCarbohydrate() ?? 0) * ($quantityInGrams / 100);
                $totalProteine += ($foodComposition->getProtein() ?? 0) * ($quantityInGrams / 100);
            }
        }

        return $this->render('meal/show.html.twig', [
            'meal' => $meal,
            'totalKcal' => $totalKcal,
            'totalLipide' => $totalLipide,
            'totalGlucide' => $totalGlucide,
            'totalProteine' => $totalProteine,
            'type' => $type,
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
