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

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $num_vol = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $aeroport_depart = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $aeroport_arrivee = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeInterface $date_depart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeInterface $date_arrivee = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Range(min: 0)]
    private ?int $places_dispo = null;

    #[ORM\Column]
    #[Assert\NotNull]
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

    public function getId_vol(): ?int
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

    public function setDateDepart(\DateTimeInterface $date_depart): static
    {
        $this->date_depart = $date_depart;

        return $this;
    }

    public function getDateArrivee(): ?\DateTimeInterface
    {
        return $this->date_arrivee;
    }

    public function setDateArrivee(\DateTimeInterface $date_arrivee): static
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
            // set the owning side to null (unless already changed)
            if ($reservationVol->getVol() === $this) {
                $reservationVol->setVol(null);
            }
        }

        return $this;
    }

    // Méthode pour rendre l'entité plus lisible dans les formulaires
    public function __toString(): string
    {
        return $this->num_vol ?? 'Vol';
    }
}
