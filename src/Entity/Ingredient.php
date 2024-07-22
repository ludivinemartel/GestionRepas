<?php

namespace App\Entity;

use App\Repository\IngredientRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IngredientRepository::class)]
class Ingredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $Name = null;

    #[ORM\Column]
    private ?int $Quantity = null;

    #[ORM\Column(length: 50)]
    private ?string $Mesure = null;

    #[ORM\ManyToOne(inversedBy: 'Ingredients', cascade: ['persist'])]
    private ?Meal $meal = null;

    #[ORM\ManyToOne(targetEntity: FoodComposition::class, inversedBy: 'ingredients')]
    private ?FoodComposition $foodComposition = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->Quantity;
    }

    public function setQuantity(int $Quantity): static
    {
        $this->Quantity = $Quantity;

        return $this;
    }

    public function getMesure(): ?string
    {
        return $this->Mesure;
    }

    public function setMesure(string $Mesure): static
    {
        $this->Mesure = $Mesure;

        return $this;
    }

    public function getMeal(): ?Meal
    {
        return $this->meal;
    }

    public function setMeal(?Meal $meal): static
    {
        $this->meal = $meal;

        return $this;
    }

    public function getFoodComposition(): ?FoodComposition
    {
        return $this->foodComposition;
    }

    public function setFoodComposition(?FoodComposition $foodComposition): static
    {
        $this->foodComposition = $foodComposition;

        return $this;
    }
}
