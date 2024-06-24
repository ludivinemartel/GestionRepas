<?php

namespace App\Entity;

use App\Repository\MealRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MealRepository::class)]
class Meal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $Name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Picture = null;

    #[ORM\Column(length: 2000)]
    private ?string $Description = null;

    #[ORM\Column(nullable: true)]
    private ?int $Kcal = null;

    #[ORM\Column(nullable: true)]
    private ?int $Lipide = null;

    #[ORM\Column(nullable: true)]
    private ?int $Glucide = null;

    #[ORM\Column(nullable: true)]
    private ?int $Proteine = null;

    #[ORM\ManyToOne(inversedBy: 'meals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $Users = null;

    /**
     * @var Collection<int, Ingredient>
     */
    #[ORM\OneToMany(mappedBy: 'meal', targetEntity: Ingredient::class, cascade: ['persist', 'remove'])]
    private Collection $Ingredients;

    #[ORM\Column(nullable: true)]
    private ?int $NbPersonne = null;

    #[ORM\Column(nullable: true)]
    private ?int $time = null;

    public function __construct()
    {
        $this->Ingredients = new ArrayCollection();
    }

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

    public function getPicture(): ?string
    {
        return $this->Picture;
    }

    public function setPicture(?string $Picture): static
    {
        $this->Picture = $Picture;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getKcal(): ?int
    {
        return $this->Kcal;
    }

    public function setKcal(?int $Kcal): static
    {
        $this->Kcal = $Kcal;

        return $this;
    }

    public function getLipide(): ?int
    {
        return $this->Lipide;
    }

    public function setLipide(?int $Lipide): static
    {
        $this->Lipide = $Lipide;

        return $this;
    }

    public function getGlucide(): ?int
    {
        return $this->Glucide;
    }

    public function setGlucide(?int $Glucide): static
    {
        $this->Glucide = $Glucide;

        return $this;
    }

    public function getProteine(): ?int
    {
        return $this->Proteine;
    }

    public function setProteine(?int $Proteine): static
    {
        $this->Proteine = $Proteine;

        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->Users;
    }

    public function setUsers(?User $Users): static
    {
        $this->Users = $Users;

        return $this;
    }

    /**
     * @return Collection<int, Ingredient>
     */
    public function getIngredients(): Collection
    {
        return $this->Ingredients;
    }

    public function addIngredient(Ingredient $ingredient): static
    {
        if (!$this->Ingredients->contains($ingredient)) {
            $this->Ingredients->add($ingredient);
            $ingredient->setMeal($this);
        }

        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): static
    {
        if ($this->Ingredients->removeElement($ingredient)) {
            if ($ingredient->getMeal() === $this) {
                $ingredient->setMeal(null);
            }
        }

        return $this;
    }

    public function getNbPersonne(): ?int
    {
        return $this->NbPersonne;
    }

    public function setNbPersonne(?int $NbPersonne): static
    {
        $this->NbPersonne = $NbPersonne;

        return $this;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function setTime(?int $time): static
    {
        $this->time = $time;

        return $this;
    }
}
