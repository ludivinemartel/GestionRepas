<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\EntityListeners(['App\EntityListener\UserListener'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull()]
    private ?string $FirstName;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull()]
    private ?string $LastName;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotNull()]
    private ?string $Email;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull()]
    private ?bool $IsSubscriber;

    #[ORM\Column]
    #[Assert\NotNull()]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank()]
    private ?string $password = 'password';

    private ?string $plainPassword = null;

    /**
     * @var Collection<int, Meal>
     */
    #[ORM\OneToMany(targetEntity: Meal::class, mappedBy: 'Users')]
    private Collection $meals;

    /**
     * @var Collection<int, WeeklyMeal>
     */
    #[ORM\OneToMany(targetEntity: WeeklyMeal::class, mappedBy: 'user')]
    private Collection $weeklyMeals;

    /**
     * @var Collection<int, PantryItem>
     */
    #[ORM\OneToMany(targetEntity: PantryItem::class, mappedBy: 'user')]
    private Collection $pantryItems;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserPreference $UserPreference = null;

    public function __construct()
    {
        $this->meals = new ArrayCollection();
        $this->weeklyMeals = new ArrayCollection();
        $this->pantryItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->FirstName;
    }

    public function setFirstName(string $FirstName): self
    {
        $this->FirstName = $FirstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->LastName;
    }

    public function setLastName(string $LastName): self
    {
        $this->LastName = $LastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): self
    {
        $this->Email = $Email;

        return $this;
    }

    public function isSubscriber(): ?bool
    {
        return $this->IsSubscriber;
    }

    public function setIsSubscriber(bool $IsSubscriber): self
    {
        $this->IsSubscriber = $IsSubscriber;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->Email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection<int, Meal>
     */
    public function getMeals(): Collection
    {
        return $this->meals;
    }

    public function addMeal(Meal $meal): static
    {
        if (!$this->meals->contains($meal)) {
            $this->meals->add($meal);
            $meal->setUser($this);
        }

        return $this;
    }

    public function removeMeal(Meal $meal): static
    {
        if ($this->meals->removeElement($meal)) {
            // set the owning side to null (unless already changed)
            if ($meal->getUser() === $this) {
                $meal->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WeeklyMeal>
     */
    public function getWeeklyMeals(): Collection
    {
        return $this->weeklyMeals;
    }

    public function addWeeklyMeal(WeeklyMeal $weeklyMeal): static
    {
        if (!$this->weeklyMeals->contains($weeklyMeal)) {
            $this->weeklyMeals->add($weeklyMeal);
            $weeklyMeal->setUser($this);
        }

        return $this;
    }

    public function removeWeeklyMeal(WeeklyMeal $weeklyMeal): static
    {
        if ($this->weeklyMeals->removeElement($weeklyMeal)) {
            // set the owning side to null (unless already changed)
            if ($weeklyMeal->getUser() === $this) {
                $weeklyMeal->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PantryItem>
     */
    public function getPantryItems(): Collection
    {
        return $this->pantryItems;
    }

    public function addPantryItem(PantryItem $pantryItem): static
    {
        if (!$this->pantryItems->contains($pantryItem)) {
            $this->pantryItems->add($pantryItem);
            $pantryItem->setUser($this);
        }

        return $this;
    }

    public function removePantryItem(PantryItem $pantryItem): static
    {
        if ($this->pantryItems->removeElement($pantryItem)) {
            // set the owning side to null (unless already changed)
            if ($pantryItem->getUser() === $this) {
                $pantryItem->setUser(null);
            }
        }

        return $this;
    }

    public function getUserPreference(): ?UserPreference
    {
        return $this->UserPreference;
    }

    public function setUserPreference(?UserPreference $UserPreference): static
    {
        $this->UserPreference = $UserPreference;

        // set (or unset) the owning side of the relation if necessary
        $newUser = $UserPreference === null ? null : $this;
        if ($newUser !== $UserPreference->getUser()) {
            $UserPreference->setUser($newUser);
        }

        return $this;
    }
}
