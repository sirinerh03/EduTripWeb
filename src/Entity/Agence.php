<?php
// src/Entity/Agence.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Pack_agence;

#[ORM\Entity]
class Agence
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private $id_agence;

    #[ORM\Column(type: "date", nullable: true)]
    #[Assert\NotBlank(message: "La date de création est obligatoire.")]
    #[Assert\GreaterThanOrEqual("today", message: "La date de création doit être supérieure ou égale à aujourd'hui.")]
    private ?\DateTimeInterface $date_creation;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "Le numéro de téléphone est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^\d{8}$/",
        message: "Le numéro de téléphone doit contenir exactement 8 chiffres."
    )]
    private int $telephone_ag;

    #[ORM\Column(type: "string", length: 200)]
    #[Assert\NotBlank(message: "Le nom de l'agence est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ\s'-]+$/u",
        message: "Le nom de l'agence ne doit contenir que des lettres."
    )]
    private string $nom_ag;

    #[ORM\Column(type: "string", length: 200)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire.")]
    private string $adresse_ag;

    #[ORM\Column(type: "string", length: 200)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "Le format de l'email est invalide. Il doit contenir un '@' et un '.'.")]
    private string $email_ag;

    #[ORM\Column(type: "string", length: 200)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(min: 10, minMessage: "La description doit contenir au moins {{ limit }} caractères.")]
    private string $description_ag;

    #[ORM\OneToMany(mappedBy: "id_agence", targetEntity: Pack_agence::class)]
    private Collection $pack_agences;

    public function __construct()
    {
        // Initialisation de la collection
        $this->pack_agences = new ArrayCollection();
        // Initialisation de la date_creation avec la date actuelle si elle est null
        $this->date_creation = new \DateTime();
    }

    public function getIdAgence(): ?int
    {
        return $this->id_agence;
    }

    public function setIdAgence(int $value): self
    {
        $this->id_agence = $value;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $value): self
    {
        $this->date_creation = $value;
        return $this;
    }

    public function getTelephoneAg(): ?int
    {
        return $this->telephone_ag;
    }

    public function setTelephoneAg(int $value): self
    {
        $this->telephone_ag = $value;
        return $this;
    }

    public function getNomAg(): ?string
    {
        return $this->nom_ag;
    }

    public function setNomAg(string $value): self
    {
        $this->nom_ag = $value;
        return $this;
    }

    public function getAdresseAg(): ?string
    {
        return $this->adresse_ag;
    }

    public function setAdresseAg(string $value): self
    {
        $this->adresse_ag = $value;
        return $this;
    }

    public function getEmailAg(): ?string
    {
        return $this->email_ag;
    }

    public function setEmailAg(string $value): self
    {
        $this->email_ag = $value;
        return $this;
    }

    public function getDescriptionAg(): ?string
    {
        return $this->description_ag;
    }

    public function setDescriptionAg(string $value): self
    {
        $this->description_ag = $value;
        return $this;
    }

    public function getPackAgences(): Collection
    {
        return $this->pack_agences;
    }

    public function addPackAgence(Pack_agence $pack_agence): self
    {
        if (!$this->pack_agences->contains($pack_agence)) {
            $this->pack_agences[] = $pack_agence;
            $pack_agence->setIdAgence($this);
        }

        return $this;
    }

    public function removePackAgence(Pack_agence $pack_agence): self
    {
        if ($this->pack_agences->removeElement($pack_agence)) {
            if ($pack_agence->getIdAgence() === $this) {
                $pack_agence->setIdAgence(null);
            }
        }

        return $this;
    }
}
