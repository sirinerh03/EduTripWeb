<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\University;

#[ORM\Entity]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $cv = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $lettre_motivation = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $diplome = null;


    #[ORM\Column(type: "string", length: 30)]
    private string $etat = 'en_attente';

    #[ORM\ManyToOne(targetEntity: University::class, inversedBy: "candidatures")]
    #[ORM\JoinColumn(name: 'idUniversity', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private University $university;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(?string $cv): self
    {
        $this->cv = $cv;
        return $this;
    }

    public function getLettreMotivation(): ?string
    {
        return $this->lettre_motivation;
    }

    public function setLettreMotivation(?string $lettre_motivation): self
    {
        $this->lettre_motivation = $lettre_motivation;
        return $this;
    }

    public function getDiplome(): ?string
    {
        return $this->diplome;
    }

    public function setDiplome(?string $diplome): self
    {
        $this->diplome = $diplome;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;
        return $this;
    }

    public function getUniversity(): ?University
    {
        return $this->university;
    }

    public function getUniversityId(): ?int
    {
        return $this->university ? $this->university->getId() : null;
    }

    public function setUniversity(?University $university): self
    {
        $this->university = $university;
        return $this;
    }
}
