<?php

namespace App\Controller;

use App\Repository\FoodCompositionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
}
