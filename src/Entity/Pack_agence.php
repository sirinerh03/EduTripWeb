<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Agence;

#[ORM\Entity]
#[ORM\Table(name: 'pack_agence')]
class Pack_agence
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: "integer")]
    private int $id_pack;

    #[ORM\ManyToOne(targetEntity: Agence::class, inversedBy: "pack_agences")]
    #[ORM\JoinColumn(name: 'id_agence', referencedColumnName: 'id_agence', onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "L'agence est obligatoire.")]
    private Agence $id_agence;

    #[ORM\Column(type: "float")]
    #[Assert\Positive(message: "Le prix doit être un nombre positif.")]
    #[Assert\NotBlank(message: "Le prix est obligatoire.")]
    private float $prix;

    #[ORM\Column(type: "integer")]
    #[Assert\GreaterThan(value: 0, message: "La durée doit être supérieure à 0.")]
    #[Assert\NotBlank(message: "La durée est obligatoire.")]
    private int $duree;

    #[ORM\Column(type: "string", length: 200)]
    #[Assert\NotBlank(message: "Les services inclus sont obligatoires.")]
    #[Assert\Regex(
        pattern: "/^[^0-9]*$/",
        message: "Les services inclus ne doivent pas contenir de chiffres."
    )]
    private string $services_inclus;

    #[ORM\Column(type: "date")]
    #[Assert\NotNull(message: "La date d'ajout est obligatoire.")]
    #[Assert\GreaterThanOrEqual("today", message: "La date d'ajout ne peut pas être inférieure à aujourd'hui.")]
    private \DateTimeInterface $date_ajout;

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    private string $status;

    #[ORM\Column(type: "string", length: 200)]
    #[Assert\NotBlank(message: "Le nom du pack est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^[^0-9]*$/",
        message: "Le nom du pack ne doit pas contenir de chiffres."
    )]
    private string $nom_pk;

    #[ORM\Column(type: "string", length: 200)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins {{ limit }} caractères."
    )]
    private string $description_pk;

    // Constructor to set default values
    public function __construct()
    {
        // Initialize date_ajout with the current date
        $this->date_ajout = new \DateTime(); // Set default to current date
    }

    // --- Getters et Setters ---
    public function getIdPack(): int
    {
        return $this->id_pack;
    }

    public function setIdPack(int $value): void
    {
        $this->id_pack = $value;
    }

    public function getIdAgence(): Agence
    {
        return $this->id_agence;
    }

    public function setIdAgence(Agence $value): void
    {
        $this->id_agence = $value;
    }

    public function getPrix(): float
    {
        return $this->prix;
    }

    public function setPrix(float $value): void
    {
        $this->prix = $value;
    }

    public function getDuree(): int
    {
        return $this->duree;
    }

    public function setDuree(int $value): void
    {
        $this->duree = $value;
    }

    public function getServicesInclus(): string
    {
        return $this->services_inclus;
    }

    public function setServicesInclus(string $value): void
    {
        $this->services_inclus = $value;
    }

    public function getDateAjout(): \DateTimeInterface
    {
        return $this->date_ajout;
    }

    public function setDateAjout(\DateTimeInterface $value): void
    {
        $this->date_ajout = $value;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $value): void
    {
        $this->status = $value;
    }

    public function getNomPk(): string
    {
        return $this->nom_pk;
    }

    public function setNomPk(string $value): void
    {
        $this->nom_pk = $value;
    }

    public function getDescriptionPk(): string
    {
        return $this->description_pk;
    }

    public function setDescriptionPk(string $value): void
    {
        $this->description_pk = $value;
    }
}
