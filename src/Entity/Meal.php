<?php

namespace App\Entity;

use App\Repository\MealRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MealRepository::class)]
#[Vich\Uploadable]
class Meal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $Name = null;

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
    private ?User $user = null;

    #[Vich\UploadableField(mapping: 'meals', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Ingredient>
     */
    #[ORM\OneToMany(mappedBy: 'meal', targetEntity: Ingredient::class, cascade: ['persist', 'remove'])]
    private Collection $Ingredients;

    #[ORM\Column(nullable: true)]
    private ?int $NbPersonne = null;

    #[ORM\Column(nullable: true)]
    private ?int $time = null;

    /**
     * @var Collection<int, Categorie>
     */
    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'meals')]
    private Collection $Categories;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $Daily = null;

    public function __construct()
    {
        $this->Ingredients = new ArrayCollection();
        $this->Categories = new ArrayCollection();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $Users): static
    {
        $this->user = $Users;

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

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    /**
     * @return Collection<int, Categorie>
     */
    public function getCategories(): Collection
    {
        return $this->Categories;
    }

    public function addCategory(Categorie $category): static
    {
        if (!$this->Categories->contains($category)) {
            $this->Categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Categorie $category): static
    {
        $this->Categories->removeElement($category);

        return $this;
    }

    public function getDaily(): ?array
    {
        return $this->Daily;
    }

    public function setDaily(?array $Daily): static
    {
        $this->Daily = $Daily;

        return $this;
    }
}
