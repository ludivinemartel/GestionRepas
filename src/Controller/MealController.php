<?php

namespace App\Controller;

use App\Entity\Meal;
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

    #[Route('/new', name: 'app_meal_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $meal = new Meal();
        $form = $this->createForm(MealType::class, $meal);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $meal->setUsers($this->getUser());
            foreach ($meal->getIngredients() as $ingredient) {
                $ingredient->setMeal($meal);
            }
            $em->persist($meal);
            $em->flush();

            return $this->redirectToRoute('user_dashboard');
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
