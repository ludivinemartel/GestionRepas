<?php

namespace App\Entity;

use App\Repository\WeeklyMealRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeeklyMealRepository::class)]
class WeeklyMeal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'weeklyMeals')]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $weekStart = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $weekEnd = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $meals = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getWeekStart(): ?\DateTimeInterface
    {
        return $this->weekStart;
    }

    public function setWeekStart(?\DateTimeInterface $weekStart): static
    {
        $this->weekStart = $weekStart;

        return $this;
    }

    public function getWeekEnd(): ?\DateTimeInterface
    {
        return $this->weekEnd;
    }

    public function setWeekEnd(?\DateTimeInterface $weekEnd): static
    {
        $this->weekEnd = $weekEnd;

        return $this;
    }

    public function getMeals(): ?array
    {
        return $this->meals;
    }

    public function setMeals(?array $meals): static
    {
        $this->meals = $meals;

        return $this;
    }
}
