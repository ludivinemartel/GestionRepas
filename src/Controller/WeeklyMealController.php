<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Entity\WeeklyMeal;
use App\Form\WeeklyMealType;
use App\Service\UserFiltreService;
use App\Repository\MealRepository;
use App\Repository\WeeklyMealRepository;
use App\Repository\PantryItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

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
            $dailyArray = $meal->getDaily();
            if (is_array($dailyArray)) {
                foreach ($dailyArray as $daily) {
                    $mealsByType[$daily][] = $meal;
                }
            }
        }

        $weeklyMeal = new WeeklyMeal();
        $form = $this->createForm(WeeklyMealType::class, $weeklyMeal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateRange = $form->get('dateRange')->getData();
            if ($dateRange && strpos($dateRange, ' to ') !== false) {
                [$weekStart, $weekEnd] = explode(' to ', $dateRange);

                // Convertir le format de date de d/m/Y à Y-m-d
                $weekStart = \DateTime::createFromFormat('d/m/Y', trim($weekStart));
                $weekEnd = \DateTime::createFromFormat('d/m/Y', trim($weekEnd));

                if ($weekStart === false || $weekEnd === false) {
                    $this->addFlash('error', 'Invalid date format. Please use the correct format.');
                } else {
                    $meals = [];
                    $data = $request->request->all();

                    if (isset($data['weekly_menu']['meals'])) {
                        foreach ($data['weekly_menu']['meals'] as $day => $mealsByDay) {
                            $meals[$day] = [];
                            foreach ($mealsByDay as $mealType => $subMeals) {
                                foreach ($subMeals as $key => $mealId) {
                                    $meals[$day][$mealType][$key] = (int)$mealId;
                                }
                            }
                        }
                    }

                    $weeklyMeal->setUser($this->getUser());
                    $weeklyMeal->setWeekStart($weekStart);
                    $weeklyMeal->setWeekEnd($weekEnd);
                    $weeklyMeal->setMeals($meals);

                    $em->persist($weeklyMeal);
                    $em->flush();

                    return $this->redirectToRoute('app_weekly_menu');
                }
            } else {
                $this->addFlash('error', 'Invalid date range format. Please use the correct format.');
            }
        } else {
            $this->addFlash('error', 'Form not submitted or not valid');
        }

        return $this->render('weekly_menu/index.html.twig', [
            'form' => $form->createView(),
            'mealsByType' => $mealsByType,
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
            foreach ($meals as $mealType => &$subMeals) {
                foreach ($subMeals as $key => &$mealId) {
                    $mealId = $mealRepository->find($mealId);
                }
            }
        }

        return $this->render('weekly_menu/show.html.twig', [
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'mealsByDayAndType' => $mealsByDayAndType,
            'weeklyMeal' => $weeklyMeal,
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
            $dateRange = $form->get('dateRange')->getData();
            if ($dateRange) {
                [$weekStart, $weekEnd] = explode(' to ', $dateRange);

                $meals = [];
                $data = $request->request->all();

                if (isset($data['weekly_menu']['meals'])) {
                    foreach ($data['weekly_menu']['meals'] as $day => $mealsByDay) {
                        $meals[$day] = [];
                        foreach ($mealsByDay as $mealType => $subMeals) {
                            foreach ($subMeals as $subType => $mealId) {
                                $meals[$day][$mealType][$subType] = (int)$mealId;
                            }
                        }
                    }
                }

                $weeklyMeal->setWeekStart(new \DateTime($weekStart));
                $weeklyMeal->setWeekEnd(new \DateTime($weekEnd));
                $weeklyMeal->setMeals($meals);

                $em->flush();

                $this->addFlash('success', 'Weekly menu updated!');
                return $this->redirectToRoute('app_show_weekly_menu_detail', ['id' => $id]);
            }
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
            // Ajouter des logs ou des messages pour le débogage
            $this->addFlash('error', 'No menu found for the given week. ID: ' . $id);
            return $this->redirectToRoute('app_show_archived_weekly_menu');
        }

        try {
            $em->remove($weeklyMeal);
            $em->flush();
            $this->addFlash('success', 'Weekly menu deleted!');
        } catch (\Exception $e) {
            // Ajouter des logs ou des messages pour le débogage en cas d'erreur
            $this->addFlash('error', 'Error deleting the menu: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_show_archived_weekly_menu');
    }

    #[Route('/weekly-menu/shopping-list/{id}', name: 'app_generate_shopping_list')]
    public function generateShoppingList(int $id, WeeklyMealRepository $weeklyMealRepository, MealRepository $mealRepository, PantryItemRepository $pantryItemRepository): Response
    {
        $user = $this->getUser();
        $weeklyMeal = $weeklyMealRepository->findOneBy(['user' => $user, 'id' => $id]);

        if (!$weeklyMeal) {
            throw $this->createNotFoundException('No menu found for the given week');
        }

        $mealsByDayAndType = $weeklyMeal->getMeals();
        $ingredients = [];

        // Récupérer les éléments du garde-manger de l'utilisateur
        $pantryItems = $pantryItemRepository->findBy(['user' => $user]);
        $pantryItemsMap = [];
        foreach ($pantryItems as $pantryItem) {
            $key = $pantryItem->getName() . '|' . $pantryItem->getMeasure();
            if (!isset($pantryItemsMap[$key])) {
                $pantryItemsMap[$key] = 0;
            }
            $pantryItemsMap[$key] += $pantryItem->getQuantity();
        }

        foreach ($mealsByDayAndType as $day => $meals) {
            foreach ($meals as $mealType => $mealIds) {
                // Vérifiez que mealIds est un tableau d'entiers
                if (!is_array($mealIds)) {
                    throw new \InvalidArgumentException("Invalid meal IDs for day $day, type $mealType");
                }

                foreach ($mealIds as $mealId) {
                    if (!is_int($mealId)) {
                        throw new \InvalidArgumentException("Invalid meal ID for day $day, type $mealType");
                    }

                    $meal = $mealRepository->find($mealId);
                    if ($meal) {
                        foreach ($meal->getIngredients() as $ingredient) {
                            $key = $ingredient->getName() . '|' . $ingredient->getMesure();
                            $requiredQuantity = $ingredient->getQuantity();

                            if (isset($pantryItemsMap[$key])) {
                                if ($pantryItemsMap[$key] >= $requiredQuantity) {
                                    // Si la quantité dans le garde-manger est suffisante, déduire la quantité utilisée
                                    $pantryItemsMap[$key] -= $requiredQuantity;
                                    $requiredQuantity = 0;
                                } else {
                                    // Sinon, déduire ce qui est disponible et ajouter le reste à la liste de courses
                                    $requiredQuantity -= $pantryItemsMap[$key];
                                    $pantryItemsMap[$key] = 0;
                                }
                            }

                            if ($requiredQuantity > 0) {
                                if (!isset($ingredients[$key])) {
                                    $ingredients[$key] = [
                                        'name' => $ingredient->getName(),
                                        'quantity' => 0,
                                        'measure' => $ingredient->getMesure(),
                                    ];
                                }
                                $ingredients[$key]['quantity'] += $requiredQuantity;
                            }
                        }
                    }
                }
            }
        }

        return $this->render('weekly_menu/shopping_list.html.twig', [
            'ingredients' => $ingredients,
            'weekStart' => $weeklyMeal->getWeekStart(),
            'weekEnd' => $weeklyMeal->getWeekEnd(),
            'weeklyMeal' => $weeklyMeal,
        ]);
    }




    #[Route('/weekly-menu/shopping-list/pdf/{id}', name: 'app_generate_shopping_list_pdf')]
    public function generateShoppingListPdf(int $id, WeeklyMealRepository $weeklyMealRepository, MealRepository $mealRepository): Response
    {
        $user = $this->getUser();
        $weeklyMeal = $weeklyMealRepository->findOneBy(['user' => $user, 'id' => $id]);

        if (!$weeklyMeal) {
            throw $this->createNotFoundException('No menu found for the given week');
        }

        $mealsByDayAndType = $weeklyMeal->getMeals();
        $ingredients = [];

        foreach ($mealsByDayAndType as $day => $meals) {
            foreach ($meals as $mealType => $mealIds) {
                // Vérifiez que mealIds est un tableau d'entiers
                if (!is_array($mealIds)) {
                    throw new \InvalidArgumentException("Invalid meal IDs for day $day, type $mealType");
                }

                foreach ($mealIds as $mealId) {
                    if (!is_int($mealId)) {
                        throw new \InvalidArgumentException("Invalid meal ID for day $day, type $mealType");
                    }

                    $meal = $mealRepository->find($mealId);
                    if ($meal) {
                        foreach ($meal->getIngredients() as $ingredient) {
                            $key = $ingredient->getName() . '|' . $ingredient->getMesure();
                            if (!isset($ingredients[$key])) {
                                $ingredients[$key] = [
                                    'name' => $ingredient->getName(),
                                    'quantity' => 0,
                                    'measure' => $ingredient->getMesure(),
                                ];
                            }
                            $ingredients[$key]['quantity'] += $ingredient->getQuantity();
                        }
                    }
                }
            }
        }

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('weekly_menu/shopping_list_pdf.html.twig', [
            'ingredients' => $ingredients,
            'weekStart' => $weeklyMeal->getWeekStart(),
            'weekEnd' => $weeklyMeal->getWeekEnd(),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("shopping_list.pdf", [
            "Attachment" => true
        ]);

        return new Response('', 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }



    #[Route('/weekly-menu/current', name: 'app_show_current_weekly_menu')]
    public function showCurrentWeeklyMenu(WeeklyMealRepository $weeklyMealRepository, MealRepository $mealRepository): Response
    {
        $user = $this->getUser();
        $currentDate = new \DateTime();
        $currentMenu = $weeklyMealRepository->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere('w.weekStart <= :currentDate')
            ->andWhere('w.weekEnd >= :currentDate')
            ->setParameter('user', $user)
            ->setParameter('currentDate', $currentDate)
            ->setMaxResults(1) // Limiter les résultats à 1 pour éviter l'erreur
            ->getQuery()
            ->getOneOrNullResult();

        if ($currentMenu) {
            $mealsByDayAndType = $currentMenu->getMeals();
            $weekStart = $currentMenu->getWeekStart();
            $weekEnd = $currentMenu->getWeekEnd();

            if (is_array($mealsByDayAndType)) {
                foreach ($mealsByDayAndType as $day => &$meals) {
                    if (is_array($meals)) {
                        foreach ($meals as $mealType => &$subMeals) {
                            if (is_array($subMeals)) {
                                foreach ($subMeals as $key => &$mealId) {
                                    $mealId = $mealRepository->find($mealId);
                                }
                            }
                        }
                    }
                }
            }

            return $this->render('weekly_menu/show.html.twig', [
                'weekStart' => $weekStart,
                'weekEnd' => $weekEnd,
                'mealsByDayAndType' => $mealsByDayAndType,
                'weeklyMeal' => $currentMenu,
            ]);
        } else {
            return $this->render('weekly_menu/show.html.twig', [
                'weekStart' => null,
                'weekEnd' => null,
                'mealsByDayAndType' => null,
                'weeklyMeal' => null,
            ]);
        }
    }

    #[Route('/weekly-menu/archive', name: 'app_show_archived_weekly_menu')]
    public function showArchivedWeeklyMenu(UserFiltreService $userFiltreService, WeeklyMealRepository $weeklyMealRepository): Response
    {
        $user = $this->getUser();
        $currentDate = new \DateTime();
        $archivedMenus = $weeklyMealRepository->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere('w.weekEnd < :currentDate')
            ->setParameter('user', $user)
            ->setParameter('currentDate', $currentDate)
            ->orderBy('w.weekStart', 'ASC') // Trier par date de début de semaine
            ->getQuery()
            ->getResult();

        $menusByYearMonth = [];

        foreach ($archivedMenus as $menu) {
            $year = $menu->getWeekEnd()->format('Y');
            $month = $menu->getWeekEnd()->format('F');
            if (!isset($menusByYearMonth[$year])) {
                $menusByYearMonth[$year] = [];
            }
            if (!isset($menusByYearMonth[$year][$month])) {
                $menusByYearMonth[$year][$month] = [];
            }
            $menusByYearMonth[$year][$month][] = $menu;
        }

        return $this->render('weekly_menu/list.html.twig', [
            'menusByYearMonth' => $menusByYearMonth,
        ]);
    }
}
