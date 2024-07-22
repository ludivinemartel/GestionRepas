<?php

namespace App\Controller;

use App\Entity\UserPreference;
use App\Form\UserPreferenceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserPreferenceController extends AbstractController
{
    #[Route('/user/preferences', name: 'app_user_preferences')]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $userPreference = $user->getUserPreference();

        if (!$userPreference) {
            $userPreference = new UserPreference();
            $userPreference->setUser($user);
        }

        $form = $this->createForm(UserPreferenceType::class, $userPreference);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $diet = $userPreference->getDiet();
            $numberOfChildren = $userPreference->getNumberOfChildren();

            // Set default categories based on diet
            if ($diet == 'vegan') {
                $userPreference->setStockCategories(array_diff($userPreference->getStockCategories(), ['Viande', 'Poisson', 'Produits Laitiers']));
            } elseif ($diet == 'vegetarian') {
                $userPreference->setStockCategories(array_diff($userPreference->getStockCategories(), ['Viande', 'Poisson']));
            }

            // Remove 'Enfant' category if no children
            if ($numberOfChildren === 0) {
                $userPreference->setStockCategories(array_diff($userPreference->getStockCategories(), ['Enfant']));
            }

            $em->persist($userPreference);
            $em->flush();

            $this->addFlash('success', 'Preferences updated successfully.');

            return $this->redirectToRoute('user_dashboard');
        }

        return $this->render('user_preferences/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
