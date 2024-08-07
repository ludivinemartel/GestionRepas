<?php

namespace App\Entity;

use App\Repository\UserPreferenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPreferenceRepository::class)]
class UserPreference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $palette = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'userPreference')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?array $StockCategories = null;

    #[ORM\Column(nullable: true)]
    private ?int $numberOfAdults = null;

    #[ORM\Column(nullable: true)]
    private ?int $numberOfChildren = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $diet = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPalette(): ?string
    {
        return $this->palette;
    }

    public function setPalette(?string $palette): static
    {
        $this->palette = $palette;

        return $this;
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

    public function getStockCategories(): ?array
    {
        return $this->StockCategories;
    }

    public function setStockCategories(?array $StockCategories): static
    {
        $this->StockCategories = $StockCategories;

        return $this;
    }

    public function getNumberOfAdults(): ?int
    {
        return $this->numberOfAdults;
    }

    public function setNumberOfAdults(?int $numberOfAdults): static
    {
        $this->numberOfAdults = $numberOfAdults;

        return $this;
    }

    public function getNumberOfChildren(): ?int
    {
        return $this->numberOfChildren;
    }

    public function setNumberOfChildren(?int $numberOfChildren): static
    {
        $this->numberOfChildren = $numberOfChildren;

        return $this;
    }

    public function getDiet(): ?string
    {
        return $this->diet;
    }

    public function setDiet(?string $diet): static
    {
        $this->diet = $diet;

        return $this;
    }
}
