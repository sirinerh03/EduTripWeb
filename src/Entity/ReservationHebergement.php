<?php

namespace App\Entity;

use App\Repository\ReservationHebergementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationHebergementRepository::class)]
class ReservationHebergement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_reservationh = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: "La date de début est obligatoire.")]
    #[Assert\GreaterThanOrEqual("today", message: "La date de début ne peut pas être avant aujourd'hui.")]
    private ?\DateTimeInterface $date_d = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire.")]
    #[Assert\GreaterThan(propertyPath: "date_d", message: "La date de fin doit être après la date de début.")]
    private ?\DateTimeInterface $date_f = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    private ?string $status = null;

    #[ORM\ManyToOne(targetEntity: Hebergement::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: 'id_hebergement', referencedColumnName: 'id_hebergement', onDelete: 'CASCADE')]
    private ?Hebergement $hebergement = null;

    public function getIdReservationh(): ?int
    {
        return $this->id_reservationh;
    }

    public function getDateD(): ?\DateTimeInterface
    {
        return $this->date_d;
    }

    public function setDateD(?\DateTimeInterface $date_d): static
    {
        $this->date_d = $date_d;
        return $this;
    }

    public function getDateF(): ?\DateTimeInterface
    {
        return $this->date_f;
    }

    public function setDateF(?\DateTimeInterface $date_f): static
    {
        $this->date_f = $date_f;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getHebergement(): ?Hebergement
    {
        return $this->hebergement;
    }

    public function setHebergement(?Hebergement $hebergement): static
    {
        $this->hebergement = $hebergement;
        return $this;
    }
}