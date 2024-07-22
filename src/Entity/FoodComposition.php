<?php

namespace App\Entity;

use App\Repository\FoodCompositionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FoodCompositionRepository::class)]
class FoodComposition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?float $kcal = null;

    #[ORM\Column(nullable: true)]
    private ?float $protein = null;

    #[ORM\Column(nullable: true)]
    private ?float $fat = null;

    #[ORM\Column(nullable: true)]
    private ?float $carbohydrate = null;

    #[ORM\OneToMany(targetEntity: Ingredient::class, mappedBy: 'foodComposition')]
    private Collection $ingredients;

    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getKcal(): ?float
    {
        return $this->kcal;
    }

    public function setKcal(?float $kcal): static
    {
        $this->kcal = $kcal;

        return $this;
    }

    public function getProtein(): ?float
    {
        return $this->protein;
    }

    public function setProtein(?float $protein): static
    {
        $this->protein = $protein;

        return $this;
    }

    public function getFat(): ?float
    {
        return $this->fat;
    }

    public function setFat(?float $fat): static
    {
        $this->fat = $fat;

        return $this;
    }

    public function getCarbohydrate(): ?float
    {
        return $this->carbohydrate;
    }

    public function setCarbohydrate(?float $carbohydrate): static
    {
        $this->carbohydrate = $carbohydrate;

        return $this;
    }

    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(Ingredient $ingredient): static
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
            $ingredient->setFoodComposition($this);
        }

        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): static
    {
        if ($this->ingredients->removeElement($ingredient)) {
            if ($ingredient->getFoodComposition() === $this) {
                $ingredient->setFoodComposition(null);
            }
        }

        return $this;
    }
}
