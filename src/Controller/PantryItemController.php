<?php

namespace App\Controller;

use App\Entity\PantryItem;
use App\Form\PantryItemType;
use App\Repository\PantryItemRepository;
use App\Entity\PantryItemCategory;
use App\Repository\PantryItemCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/pantry/item')]
class PantryItemController extends AbstractController
{
    #[Route('/', name: 'app_pantry_item_index', methods: ['GET'])]
    public function index(PantryItemRepository $pantryItemRepository, PantryItemCategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findBy(['User' => $this->getUser()], ['position' => 'ASC']);
        $pantryItems = $pantryItemRepository->findBy(['user' => $this->getUser()]);

        return $this->render('pantry_item/index.html.twig', [
            'categories' => $categories,
            'pantry_items' => $pantryItems,
        ]);
    }

    #[Route('/new', name: 'app_pantry_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $pantryItem = new PantryItem();
        $form = $this->createForm(PantryItemType::class, $pantryItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $pantryItem->setUser($user);

            $entityManager->persist($pantryItem);
            $entityManager->flush();

            return $this->redirectToRoute('app_pantry_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pantry_item/new.html.twig', [
            'pantry_item' => $pantryItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pantry_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PantryItem $pantryItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PantryItemType::class, $pantryItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_pantry_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pantry_item/edit.html.twig', [
            'pantry_item' => $pantryItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pantry_item_delete', methods: ['POST'])]
    public function delete(Request $request, PantryItem $pantryItem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $pantryItem->getId(), $request->get('_token'))) {
            $entityManager->remove($pantryItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pantry_item_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/update', name: 'app_pantry_item_update', methods: ['POST'])]
    public function update(Request $request, PantryItem $pantryItem, EntityManagerInterface $entityManager): Response
    {
        $inStock = $request->request->get('InStock');

        if ($inStock === '1') {
            $pantryItem->setInStock(true);
        } elseif ($inStock === '0') {
            $pantryItem->setInStock(false);
        } else {
            $pantryItem->setInStock(null);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_pantry_item_index');
    }

    #[Route('/update-category-order', name: 'app_update_order', methods: ['POST'])]
    public function updateCategoryOrder(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        foreach ($data['order'] as $category) {
            $categoryEntity = $entityManager->getRepository(PantryItemCategory::class)->find($category['id']);
            if ($categoryEntity) {
                $categoryEntity->setPosition($category['position']);
                $entityManager->persist($categoryEntity);
            }
        }

        $entityManager->flush();

        return new Response('Order updated', Response::HTTP_OK);
    }
}
