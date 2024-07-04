<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Entity\Ingredient;
use App\Entity\FoodComposition;
use App\Form\MealType;
use App\Form\CategorieFilterType;
use App\Repository\MealRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/meal')]
class MealController extends AbstractController
{
    #[Route('/', name: 'app_meal_index', methods: ['GET', 'POST'])]
    public function index(Request $request, MealRepository $mealRepository, Security $security): Response
    {
        $user = $security->getUser();
        $form = $this->createForm(CategorieFilterType::class);
        $form->handleRequest($request);

        $meals = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $categorie = $form->get('categorie')->getData();

            if ($categorie) {
                $meals = $mealRepository->findByUserAndCategory($user, $categorie->getId());
            } else {
                $meals = $mealRepository->findByUser($user);
            }
        } else {
            $meals = $mealRepository->findByUser($user);
        }

        return $this->render('meal/index.html.twig', [
            'form' => $form->createView(),
            'meals' => $meals,
        ]);
    }

    #[Route('/new', name: 'app_meal_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $meal = new Meal();
        $form = $this->createForm(MealType::class, $meal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer l'utilisateur connecté au repas
            $user = $security->getUser();
            if ($user) {
                $meal->setUser($user);
            } else {
                throw new \Exception('User must be logged in to create a meal.');
            }

            // Get the ingredients data from the hidden field
            $ingredientDataJson = $form->get('ingredients_data')->getData();
            $ingredientData = json_decode($ingredientDataJson, true);

            if (is_array($ingredientData)) {
                foreach ($ingredientData as $ingredientItem) {
                    $ingredient = new Ingredient();
                    $ingredient->setName($ingredientItem['name']);
                    $ingredient->setQuantity($ingredientItem['quantity']);
                    $ingredient->setMesure($ingredientItem['measure']);

                    // Set the FoodComposition entity
                    $foodComposition = $entityManager->getRepository(FoodComposition::class)->find($ingredientItem['id']);
                    if ($foodComposition) {
                        $ingredient->setFoodCompositionId($foodComposition);
                    } else {
                        throw new \Exception('Invalid FoodComposition ID.');
                    }

                    $meal->addIngredient($ingredient);
                }
            } else {
                // Log or handle the error of invalid ingredient data
                $this->addFlash('error', 'Invalid ingredient data.');
                return $this->render('meal/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $entityManager->persist($meal);
            $entityManager->flush();

            $this->addFlash('success', 'Meal created successfully with ingredients.');

            return $this->redirectToRoute('app_meal_index');
        }

        return $this->render('meal/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_meal_show', methods: ['GET'])]
    public function show(Meal $meal): Response
    {
        return $this->render('meal/show.html.twig', [
            'meal' => $meal,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_meal_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Meal $meal, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MealType::class, $meal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_meal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('meal/edit.html.twig', [
            'meal' => $meal,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_meal_delete', methods: ['POST'])]
    public function delete(Request $request, Meal $meal, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $meal->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($meal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_meal_index', [], Response::HTTP_SEE_OTHER);
    }
}
