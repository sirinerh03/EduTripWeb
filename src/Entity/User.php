<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'mail', type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas un email valide')]
    private ?string $mail = null;

    #[ORM\Column(name: 'role', type: 'string', length: 20)]
    #[Assert\NotBlank(message: 'Le rôle est obligatoire')]
    #[Assert\Choice(
        choices: ['ROLE_USER', 'ROLE_AGENCY', 'ROLE_ADMIN'],
        message: 'Le rôle doit être soit ROLE_USER, ROLE_AGENCY ou ROLE_ADMIN'
    )]
    private ?string $role = null;

    #[ORM\Column(name: 'password', type: 'string')]
    private ?string $password = null;

    #[ORM\Column(name: 'prenom', type: 'string', length: 50)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Le prénom doit faire au moins {{ limit }} caractères',
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $prenom = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 50)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Le nom doit faire au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $nom = null;

    #[ORM\Column(name: 'tel', type: 'string', length: 8)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone est obligatoire')]
    #[Assert\Regex(
        pattern: '/^[0-9]{8}$/',
        message: 'Le numéro de téléphone doit contenir 8 chiffres'
    )]
    private ?string $tel = null;

    #[ORM\Column(name: 'status', type: 'string', length: 20)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire')]
    #[Assert\Choice(
        choices: ['active', 'inactive'],
        message: 'Le statut doit être soit active soit inactive'
    )]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Review::class, orphanRemoval: true)]
    private Collection $reviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->role = 'ROLE_USER';
        $this->status = 'active';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUser(): ?int
    {
        return $this->id;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->mail;
    }

    public function getRoles(): array
    {
        $roles = [$this->role];
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

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
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(string $tel): static
    {
        $this->tel = $tel;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setUser($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getUser() === $this) {
                $review->setUser(null);
            }
        }

        return $this;
    }
}
