<?php

namespace App\Controller;

use App\Entity\PantryItem;
use App\Form\PantryItemType;
use App\Repository\PantryItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pantry/item')]
class PantryItemController extends AbstractController
{
    #[Route('/', name: 'app_pantry_item_index', methods: ['GET'])]
    public function index(PantryItemRepository $pantryItemRepository): Response
    {
        return $this->render('pantry_item/index.html.twig', [
            'pantry_items' => $pantryItemRepository->findAll(),
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


    #[Route('/{id}', name: 'app_pantry_item_show', methods: ['GET'])]
    public function show(PantryItem $pantryItem): Response
    {
        return $this->render('pantry_item/show.html.twig', [
            'pantry_item' => $pantryItem,
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
        if ($this->isCsrfTokenValid('delete' . $pantryItem->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pantryItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pantry_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
