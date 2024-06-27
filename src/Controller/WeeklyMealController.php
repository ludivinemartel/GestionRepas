<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Entity\WeeklyMeal;
use App\Form\WeeklyMealType;
use App\Service\UserFiltreService;
use App\Repository\MealRepository;
use App\Repository\WeeklyMealRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeeklyMealController extends AbstractController
{
    #[Route('/weekly-menu', name: 'app_weekly_menu')]
    public function index(Request $request, UserFiltreService $userFiltreService, MealRepository $mealRepository, EntityManagerInterface $em): Response
    {
        $allMeals = $userFiltreService->getEntitiesForCurrentUser(Meal::class);

        $mealsByType = [
            'breakfast' => [],
            'lunch' => [],
            'snack' => [],
            'dinner' => []
        ];

        foreach ($allMeals as $meal) {
            foreach ($meal->getDaily() as $daily) {
                $mealsByType[$daily][] = $meal;
            }
        }

        $weeklyMeal = new WeeklyMeal();
        $form = $this->createForm(WeeklyMealType::class, $weeklyMeal);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $weekStart = $form->get('weekStart')->getData();
            $weekEnd = $form->get('weekEnd')->getData();

            $meals = [];
            $data = $request->request->all();

            if (isset($data['weekly_menu']['meals'])) {
                foreach ($data['weekly_menu']['meals'] as $day => $mealsByDay) {
                    $meals[$day] = [];
                    foreach ($mealsByDay as $mealType => $mealId) {
                        $meals[$day][$mealType] = (int)$mealId;
                    }
                }
            }

            $weeklyMeal->setUser($this->getUser());
            $weeklyMeal->setWeekStart($weekStart);
            $weeklyMeal->setWeekEnd($weekEnd);
            $weeklyMeal->setMeals($meals);

            $em->persist($weeklyMeal);
            $em->flush();

            $this->addFlash('success', 'Weekly menu updated!');
            return $this->redirectToRoute('app_weekly_menu');
        }

        return $this->render('weekly_menu/index.html.twig', [
            'form' => $form->createView(),
            'mealsByType' => $mealsByType,
        ]);
    }

    #[Route('/weekly-menu/show', name: 'app_show_weekly_menu')]
    public function show(UserFiltreService $userFiltreService, WeeklyMealRepository $weeklyMealRepository): Response
    {
        $user = $this->getUser();
        $weeklyMeals = $weeklyMealRepository->findBy(['user' => $user]);

        $menusByWeek = [];

        foreach ($weeklyMeals as $weeklyMeal) {
            $id = $weeklyMeal->getId();
            if (!isset($menusByWeek[$id])) {
                $menusByWeek[$id] = [
                    'weekStart' => $weeklyMeal->getWeekStart(),
                    'weekEnd' => $weeklyMeal->getWeekEnd(),
                    'meals' => $weeklyMeal->getMeals()
                ];
            }
        }

        return $this->render('weekly_menu/list.html.twig', [
            'menusByWeek' => $menusByWeek,
        ]);
    }

    #[Route('/weekly-menu/show/{id}', name: 'app_show_weekly_menu_detail')]
    public function showDetail(int $id, WeeklyMealRepository $weeklyMealRepository, MealRepository $mealRepository): Response
    {
        $user = $this->getUser();
        $weeklyMeal = $weeklyMealRepository->findOneBy(['user' => $user, 'id' => $id]);

        if (!$weeklyMeal) {
            throw $this->createNotFoundException('No menu found for the given week');
        }

        $mealsByDayAndType = $weeklyMeal->getMeals();
        $weekStart = $weeklyMeal->getWeekStart();
        $weekEnd = $weeklyMeal->getWeekEnd();

        // Convert meal IDs to meal entities
        foreach ($mealsByDayAndType as $day => &$meals) {
            foreach ($meals as $mealType => &$mealId) {
                $mealId = $mealRepository->find($mealId);
            }
        }

        return $this->render('weekly_menu/show.html.twig', [
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'mealsByDayAndType' => $mealsByDayAndType,
        ]);
    }

    #[Route('/weekly-menu/edit/{id}', name: 'app_edit_weekly_menu')]
    public function edit(int $id, Request $request, WeeklyMealRepository $weeklyMealRepository, MealRepository $mealRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $weeklyMeal = $weeklyMealRepository->findOneBy(['user' => $user, 'id' => $id]);

        if (!$weeklyMeal) {
            throw $this->createNotFoundException('No menu found for the given week');
        }

        $form = $this->createForm(WeeklyMealType::class, $weeklyMeal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $weekStart = $form->get('weekStart')->getData();
            $weekEnd = $form->get('weekEnd')->getData();

            $meals = [];
            $data = $request->request->all();

            if (isset($data['weekly_menu']['meals'])) {
                foreach ($data['weekly_menu']['meals'] as $day => $mealsByDay) {
                    $meals[$day] = [];
                    foreach ($mealsByDay as $mealType => $mealId) {
                        $meals[$day][$mealType] = (int)$mealId;
                    }
                }
            }

            $weeklyMeal->setWeekStart($weekStart);
            $weeklyMeal->setWeekEnd($weekEnd);
            $weeklyMeal->setMeals($meals);

            $em->flush();

            $this->addFlash('success', 'Weekly menu updated!');
            return $this->redirectToRoute('app_show_weekly_menu_detail', ['id' => $id]);
        }

        $allMeals = $mealRepository->findBy(['user' => $user]);
        $mealsByType = [
            'breakfast' => [],
            'lunch' => [],
            'snack' => [],
            'dinner' => []
        ];

        foreach ($allMeals as $meal) {
            foreach ($meal->getDaily() as $daily) {
                $mealsByType[$daily][] = $meal;
            }
        }

        return $this->render('weekly_menu/edit.html.twig', [
            'form' => $form->createView(),
            'mealsByType' => $mealsByType,
            'weeklyMeal' => $weeklyMeal,
        ]);
    }

    #[Route('/weekly-menu/delete/{id}', name: 'app_delete_weekly_menu', methods: ['POST'])]
    public function delete(int $id, WeeklyMealRepository $weeklyMealRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $weeklyMeal = $weeklyMealRepository->findOneBy(['user' => $user, 'id' => $id]);

        if (!$weeklyMeal) {
            throw $this->createNotFoundException('No menu found for the given week');
        }

        $em->remove($weeklyMeal);
        $em->flush();

        $this->addFlash('success', 'Weekly menu deleted!');
        return $this->redirectToRoute('app_show_weekly_menu');
    }
}
