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
    private ?int $id_vol = null;
    
    #[ORM\Column(length: 7)]
    #[Assert\NotBlank(message: 'Le numéro de vol est obligatoire.')]
    #[Assert\Length(
        max: 7,
        maxMessage: 'Le numéro de vol ne doit pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[A-Za-z0-9\W]{1,7}$/',
        message: 'Le numéro de vol peut contenir lettres, chiffres et caractères spéciaux.'
    )]    
    private ?string $num_vol = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank(message: 'L’aéroport de départ est obligatoire.')]
    private ?string $aeroport_depart = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank(message: 'L’aéroport d’arrivée est obligatoire.')]
    private ?string $aeroport_arrivee = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: 'La date de départ est obligatoire.')]
    #[Assert\GreaterThan("today", message: "La date de départ doit être dans le futur.")]
    private ?\DateTimeInterface $date_depart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: 'La date d’arrivée est obligatoire.')]
    #[Assert\GreaterThan("today", message: 'La date d’arrivée doit être après la date de départ.')]
    private ?\DateTimeInterface $date_arrivee = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le nombre de places est requis.')]
    #[Assert\Type(type: 'integer', message: 'Le nombre de places doit être un entier.')]
    #[Assert\PositiveOrZero(message: 'Le nombre de places doit être positif ou nul.')]
    #[Assert\Range(min: 0)]
    private ?int $places_dispo = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le prix est requis.')]
    #[Assert\Regex(
        pattern: '/^\d+([.,]\d{1,3})?$/',
        message: 'Le prix doit être un nombre avec au maximum 3 chiffres après la virgule.'
    )]
    #[Assert\Range(min: 0)]
    private ?float $prix_vol = null;

    /**
     * @var Collection<int, ReservationVol>
     */
    #[ORM\OneToMany(targetEntity: ReservationVol::class, mappedBy: 'Vol')]
    private Collection $reservationVols;

    public function __construct()
    {
        $this->reservationVols = new ArrayCollection();
    }

    // Getters and setters

    public function getIdVol(): ?int
    {
        return $this->id_vol;
    }

    public function getNumVol(): ?string
    {
        return $this->num_vol;
    }

    public function setNumVol(string $num_vol): static
    {
        $this->num_vol = $num_vol;
        return $this;
    }

    public function getAeroportDepart(): ?string
    {
        return $this->aeroport_depart;
    }

    public function setAeroportDepart(string $aeroport_depart): static
    {
        $this->aeroport_depart = $aeroport_depart;
        return $this;
    }

    public function getAeroportArrivee(): ?string
    {
        return $this->aeroport_arrivee;
    }

    public function setAeroportArrivee(string $aeroport_arrivee): static
    {
        $this->aeroport_arrivee = $aeroport_arrivee;
        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->date_depart;
    }

    public function setDateDepart(?\DateTimeInterface $date_depart): self
{
    $this->date_depart = $date_depart;
    return $this;
}


    public function getDateArrivee(): ?\DateTimeInterface
    {
        return $this->date_arrivee;
    }

    public function setDateArrivee(?\DateTimeInterface $date_arrivee): static
    {
        $this->date_arrivee = $date_arrivee;
        return $this;
    }

    public function getPlacesDispo(): ?int
    {
        return $this->places_dispo;
    }

    public function setPlacesDispo(int $places_dispo): static
    {
        $this->places_dispo = $places_dispo;
        return $this;
    }

    public function getPrixVol(): ?float
    {
        return $this->prix_vol;
    }

    public function setPrixVol(float $prix_vol): static
    {
        $this->prix_vol = $prix_vol;
        return $this;
    }

    /**
     * @return Collection<int, ReservationVol>
     */
    public function getReservationVols(): Collection
    {
        return $this->reservationVols;
    }

    public function addReservationVol(ReservationVol $reservationVol): static
    {
        if (!$this->reservationVols->contains($reservationVol)) {
            $this->reservationVols->add($reservationVol);
            $reservationVol->setVol($this);
        }

        return $this;
    }

    public function removeReservationVol(ReservationVol $reservationVol): static
    {
        if ($this->reservationVols->removeElement($reservationVol)) {
            if ($reservationVol->getVol() === $this) {
                $reservationVol->setVol(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->num_vol ?? 'Vol';
    }
}
