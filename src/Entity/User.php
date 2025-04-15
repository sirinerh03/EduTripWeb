<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
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
    #[ORM\Column(name: 'id_user', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'prenom', type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le prénom doit faire au moins {{ limit }} caractères', maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères')]
    private ?string $prenom = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le nom doit faire au moins {{ limit }} caractères', maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères')]
    private ?string $nom = null;

    #[ORM\Column(name: 'mail', type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas un email valide')]
    private ?string $mail = null;

    #[ORM\Column(name: 'tel', type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Le téléphone est obligatoire')]
    #[Assert\Regex(pattern: '/^[0-9+\s-]{8,}$/', message: 'Le numéro de téléphone n\'est pas valide')]
    private ?string $tel = null;

    #[ORM\Column(name: 'status', type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire')]
    private ?string $status = null;

    #[ORM\Column(name: 'role', type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Le rôle est obligatoire')]
    private ?string $role = null;

    #[ORM\Column(name: 'password', type: 'string', length: 100)]
    private ?string $password = null;

    #[ORM\Column(name: 'confirm_password', type: 'string', length: 100)]
    private ?string $confirm_password = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;
        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(string $tel): self
    {
        $this->tel = $tel;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirm_password;
    }

    public function setConfirmPassword(string $confirm_password): self
    {
        $this->confirm_password = $confirm_password;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];
        if ($this->role === 'admin') {
            $roles[] = 'ROLE_ADMIN';
        }
        return array_unique($roles);
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    /**
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->mail;
    }
}
