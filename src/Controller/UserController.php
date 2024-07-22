<?php

namespace App\Controller;

use App\Repository\WeeklyMealRepository;
use App\Repository\MealRepository;
use App\Repository\PantryItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private WeeklyMealRepository $weeklyMealRepository;
    private MealRepository $mealRepository;
    private PantryItemRepository $pantryItemRepository;

    public function __construct(EntityManagerInterface $entityManager, WeeklyMealRepository $weeklyMealRepository, MealRepository $mealRepository, PantryItemRepository $pantryItemRepository)
    {
        $this->entityManager = $entityManager;
        $this->weeklyMealRepository = $weeklyMealRepository;
        $this->mealRepository = $mealRepository;
        $this->pantryItemRepository = $pantryItemRepository;
    }

    #[Route('/user/dashboard', name: 'user_dashboard')]
    public function dashboard(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // Récupérer le menu du jour
        $currentDate = new \DateTime();
        $dailyMenu = $this->weeklyMealRepository->findDailyMenu($user, $currentDate);
        $currentDay = $this->translateDayToFrench($currentDate->format('l'));

        // Table de correspondance pour les types de repas
        $mealTypeTranslations = [
            'breakfast' => 'Petit déjeuner',
            'lunch' => 'Déjeuner',
            'snack' => 'Collation',
            'dinner' => 'Dîner',
        ];

        // Convertir les identifiants des repas en objets Meal et réorganiser les types de repas
        $mealsByDayAndType = [];
        if ($dailyMenu) {
            $meals = $dailyMenu->getMeals();
            foreach ($meals as $day => $mealTypes) {
                foreach ($mealTypes as $type => $mealIds) {
                    foreach ($mealIds as $mealId) {
                        $meal = $this->mealRepository->find($mealId);
                        if ($meal) {
                            $translatedType = $mealTypeTranslations[$type] ?? $type;
                            $mealsByDayAndType[$day][$translatedType][] = $meal;
                        }
                    }
                }
            }

            // Réorganiser les types de repas pour le jour actuel
            if (isset($mealsByDayAndType[$currentDay])) {
                $mealsByDayAndType[$currentDay] = $this->reorganizeMeals($mealsByDayAndType[$currentDay]);
            }
        }

        // Récupérer la liste des éléments à acheter
        $itemsToBuy = $this->pantryItemRepository->findItemsToBuy($user);

        return $this->render('user/dashboard.html.twig', [
            'user' => $user,
            'mealsByDayAndType' => $mealsByDayAndType,
            'currentDay' => $currentDay,
            'itemsToBuy' => $itemsToBuy,
        ]);
    }

    private function translateDayToFrench(string $day): string
    {
        $days = [
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
            'Sunday' => 'Dimanche',
        ];

        return $days[$day] ?? $day;
    }

    private function reorganizeMeals(array $meals): array
    {
        $order = ['Petit déjeuner', 'Déjeuner', 'Collation', 'Dîner'];
        $organizedMeals = [];

        foreach ($order as $mealType) {
            if (isset($meals[$mealType])) {
                $organizedMeals[$mealType] = $meals[$mealType];
            }
        }

        return $organizedMeals;
    }
}
