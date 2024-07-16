<?php

namespace App\Controller;

use App\Repository\FoodCompositionRepository;
use App\Entity\FoodComposition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class IngredientController extends AbstractController
{
    #[Route('/ingredient/search', name: 'ingredient_search')]
    public function search(Request $request, FoodCompositionRepository $foodCompositionRepository): JsonResponse
    {
        $query = $request->query->get('q', '');

        $results = $foodCompositionRepository->createQueryBuilder('f')
            ->where('f.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $items = [];
        foreach ($results as $result) {
            $items[] = [
                'id' => $result->getId(),
                'text' => $result->getName(),
            ];
        }

        return new JsonResponse(['items' => $items]);
    }

    #[Route('/ingredient/add', name: 'ingredient_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['name'], $data['kcal'], $data['protein'], $data['fat'], $data['carbohydrate'])) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $foodComposition = new FoodComposition();
        $foodComposition->setName($data['name']);
        $foodComposition->setKcal($data['kcal']);
        $foodComposition->setProtein($data['protein']);
        $foodComposition->setFat($data['fat']);
        $foodComposition->setCarbohydrate($data['carbohydrate']);

        $entityManager->persist($foodComposition);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'ingredient' => [
            'id' => $foodComposition->getId(),
            'name' => $foodComposition->getName()
        ]]);
    }
}
