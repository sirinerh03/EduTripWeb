<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ReservationVolRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationVolRepository::class)]
class ReservationVol
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_reservation = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column(length: 255)]
#[Assert\NotBlank(message: "Le nom est obligatoire")]
#[Assert\Length(
    min: 3,
    max: 50,
    minMessage: "3 caractères minimum",
    maxMessage: "50 caractères maximum"
)]
#[Assert\Regex(
    pattern: "/^[a-zA-ZÀ-ÿ\s\-']+$/",
    message: "Caractères spéciaux non autorisés"
)]
private ?string $nom = null;

#[ORM\Column(length: 255)]
#[Assert\NotBlank(message: "Le prénom est obligatoire")]
#[Assert\Length(min: 3, max: 50)]
private ?string $prenom = null;

#[ORM\Column(length: 255)]
#[Assert\NotBlank(message: "L'email est obligatoire")]
#[Assert\Email(message: "Format email invalide")]
private ?string $email = null;

#[ORM\Column]
#[Assert\NotBlank(message: "L'ID étudiant est obligatoire")]
#[Assert\Regex(
    pattern: "/^[A-Z]\d{8}$/",
    message: "Format : Lettre majuscule suivie de 8 chiffres"
)]
private ?int $id_etudiant = null;

#[ORM\Column]
#[Assert\NotBlank(message: "Nombre de places requis")]
#[Assert\Positive(message: "Doit être supérieur à 0")]
#[Assert\LessThanOrEqual(
    value: 10,
    message: "Maximum 10 places par réservation"
)]
private ?int $nb_palce = null;

    #[ORM\ManyToOne(inversedBy: 'reservationVols')]
    #[ORM\JoinColumn(name:"id_vol",referencedColumnName:"id_vol",nullable: false)]
    private ?Vol $vol = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->date_reservation;
    }

    public function setDateReservation(\DateTimeInterface $date_reservation): static
    {
        $this->date_reservation = $date_reservation;

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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getIdEtudiant(): ?int
    {
        return $this->id_etudiant;
    }

    public function setIdEtudiant(int $id_etudiant): static
    {
        $this->id_etudiant = $id_etudiant;

        return $this;
    }

    public function getNbPalce(): ?int
    {
        return $this->nb_palce;
    }

    public function setNbPalce(int $nb_palce): static
    {
        $this->nb_palce = $nb_palce;

        return $this;
    }

    public function getVol(): ?Vol
    {
        return $this->vol;
    }

    public function setVol(?Vol $vol): static
    {
        $this->vol = $vol;

        return $this;
    }
}
