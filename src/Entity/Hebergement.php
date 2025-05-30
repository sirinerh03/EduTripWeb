<?php

namespace App\Entity;

use App\Repository\HebergementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HebergementRepository::class)]
class Hebergement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_hebergement = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'hébergement est obligatoire.")]
    private string $nomh;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type d'hébergement est obligatoire.")]
    private string $typeh;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse de l'hébergement est obligatoire.")]
    private string $adressh;

    #[ORM\Column]
    #[Assert\NotNull(message: "La capacité est obligatoire.")]
    #[Assert\Positive(message: "La capacité doit être un nombre positif.")]
    #[Assert\Range(
        min: 1,
        max: 10000,
        notInRangeMessage: "La capacité doit être comprise entre 1 et 10000."
    )]
    private int $capaciteh;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le prix est obligatoire.")]
    #[Assert\Positive(message: "Le prix doit être un nombre positif.")]
    #[Assert\Range(
        min: 1,
        max: 10000,
        notInRangeMessage: "Le prix doit être compris entre 1 et 10000."
    )]
    private float $prixh;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La disponibilité doit être spécifiée.")]
    private string $disponibleh;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(min: 10, minMessage: "La description doit contenir au moins {{ limit }} caractères.")]
    private string $descriptionh;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'image est obligatoire.")]
    private string $imageh;

    #[ORM\OneToMany(mappedBy: 'hebergement', targetEntity: ReservationHebergement::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $reservations;

    public const AVAILABILITY_CHOICES = [
        'Disponible' => 'Disponible',
        'Non disponible' => 'Non disponible',
        'Réservée' => 'Réservée'
    ];

    public const TYPE_CHOICES = [
        'Résidences universitaires publiques' => 'Résidences universitaires publiques',
        'Résidences étudiantes privées' => 'Résidences étudiantes privées',
        'Appartements' => 'Appartements',
        'Foyers' => 'Foyers',
    ];

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id_hebergement;
    }

    public function getNomh(): string
    {
        return $this->nomh;
    }

    public function setNomh(string $nomh): static
    {
        $this->nomh = $nomh;
        return $this;
    }

    public function getTypeh(): string
    {
        return $this->typeh;
    }

    public function setTypeh(string $typeh): static
    {
        $this->typeh = $typeh;
        return $this;
    }

    public function getAdressh(): string
    {
        return $this->adressh;
    }

    public function setAdressh(string $adressh): static
    {
        $this->adressh = $adressh;
        return $this;
    }

    public function getCapaciteh(): int
    {
        return $this->capaciteh;
    }

    public function setCapaciteh(int $capaciteh): static
    {
        $this->capaciteh = $capaciteh;
        return $this;
    }

    public function getPrixh(): float
    {
        return $this->prixh;
    }

    public function setPrixh(float $prixh): static
    {
        $this->prixh = $prixh;
        return $this;
    }

    public function getDisponibleh(): string
    {
        return $this->disponibleh;
    }

    public function setDisponibleh(string $disponibleh): static
    {
        $this->disponibleh = $disponibleh;
        return $this;
    }

    public function getDescriptionh(): string
    {
        return $this->descriptionh;
    }

    public function setDescriptionh(string $descriptionh): static
    {
        $this->descriptionh = $descriptionh;
        return $this;
    }

    public function getImageh(): string
    {
        return $this->imageh;
    }

    public function setImageh(string $imageh): static
    {
        $this->imageh = $imageh;
        return $this;
    }

    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(ReservationHebergement $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setHebergement($this);
        }

        return $this;
    }

    public function removeReservation(ReservationHebergement $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getHebergement() === $this) {
                $reservation->setHebergement(null);
            }
        }

        return $this;
    }
}