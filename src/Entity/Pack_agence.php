<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Agence;

#[ORM\Entity]
class Pack_agence
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]  // Cette ligne permet la génération automatique de l'ID
    #[ORM\Column(type: "integer")]
    private int $id_pack;

    #[ORM\ManyToOne(targetEntity: Agence::class, inversedBy: "pack_agences")]
    #[ORM\JoinColumn(name: 'id_agence', referencedColumnName: 'id_agence', onDelete: 'CASCADE')]
    private Agence $id_agence;

    #[ORM\Column(type: "float")]
    private float $prix;

    #[ORM\Column(type: "integer")]
    private int $duree;

    #[ORM\Column(type: "string", length: 200)]
    private string $services_inclus;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_ajout;

    #[ORM\Column(type: "string")]
    private string $status;

    #[ORM\Column(type: "string", length: 200)]
    private string $nom_pk;

    #[ORM\Column(type: "string", length: 200)]
    private string $description_pk;

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
