<?php

namespace App\Entity;

use App\Repository\VolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VolRepository::class)]
class Vol
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de la compagnie est obligatoire')]
    private ?string $compagnie = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le numéro de vol est obligatoire')]
    private ?string $numeroVol = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La ville de départ est obligatoire')]
    private ?string $depart = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La ville d\'arrivée est obligatoire')]
    private ?string $arrivee = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de départ est obligatoire')]
    #[Assert\GreaterThan('today', message: 'La date de départ doit être dans le futur')]
    private ?\DateTimeInterface $dateDepart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'La date d\'arrivée est obligatoire')]
    #[Assert\GreaterThan(propertyPath: 'dateDepart', message: 'La date d\'arrivée doit être après la date de départ')]
    private ?\DateTimeInterface $dateArrivee = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le prix est obligatoire')]
    #[Assert\Positive(message: 'Le prix doit être positif')]
    private ?float $prix = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le nombre de places disponibles est obligatoire')]
    #[Assert\PositiveOrZero(message: 'Le nombre de places doit être positif ou nul')]
    private ?int $placesDisponibles = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'vol', targetEntity: Reservation::class)]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompagnie(): ?string
    {
        return $this->compagnie;
    }

    public function setCompagnie(string $compagnie): static
    {
        $this->compagnie = $compagnie;
        return $this;
    }

    public function getNumeroVol(): ?string
    {
        return $this->numeroVol;
    }

    public function setNumeroVol(string $numeroVol): static
    {
        $this->numeroVol = $numeroVol;
        return $this;
    }

    public function getDepart(): ?string
    {
        return $this->depart;
    }

    public function setDepart(string $depart): static
    {
        $this->depart = $depart;
        return $this;
    }

    public function getArrivee(): ?string
    {
        return $this->arrivee;
    }

    public function setArrivee(string $arrivee): static
    {
        $this->arrivee = $arrivee;
        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->dateDepart;
    }

    public function setDateDepart(\DateTimeInterface $dateDepart): static
    {
        $this->dateDepart = $dateDepart;
        return $this;
    }

    public function getDateArrivee(): ?\DateTimeInterface
    {
        return $this->dateArrivee;
    }

    public function setDateArrivee(\DateTimeInterface $dateArrivee): static
    {
        $this->dateArrivee = $dateArrivee;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function getPlacesDisponibles(): ?int
    {
        return $this->placesDisponibles;
    }

    public function setPlacesDisponibles(int $placesDisponibles): static
    {
        $this->placesDisponibles = $placesDisponibles;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setVol($this);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getVol() === $this) {
                $reservation->setVol(null);
            }
        }
        return $this;
    }
} 