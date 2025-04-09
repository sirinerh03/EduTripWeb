<?php

// src/Entity/Agence.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Entity\Pack_agence;

#[ORM\Entity]
class Agence
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]  // Add this to make id_agence auto-increment
    #[ORM\Column(type: "integer")]
    private $id_agence;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_creation;

    #[ORM\Column(type: "integer")]
    private int $telephone_ag;

    #[ORM\Column(type: "string", length: 200)]
    private string $nom_ag;

    #[ORM\Column(type: "string", length: 200)]
    private string $adresse_ag;

    #[ORM\Column(type: "string", length: 200)]
    private string $email_ag;

    #[ORM\Column(type: "string", length: 200)]
    private string $description_ag;

    #[ORM\OneToMany(mappedBy: "id_agence", targetEntity: Pack_agence::class)]
    private Collection $pack_agences;

    public function getIdAgence()
    {
        return $this->id_agence;
    }

    public function setIdAgence($value)
    {
        $this->id_agence = $value;
    }

    public function getDateCreation()
    {
        return $this->date_creation;
    }

    public function setDateCreation($value)
    {
        $this->date_creation = $value;
    }

    public function getTelephoneAg()
    {
        return $this->telephone_ag;
    }

    public function setTelephoneAg($value)
    {
        $this->telephone_ag = $value;
    }

    public function getNomAg()
    {
        return $this->nom_ag;
    }

    public function setNomAg($value)
    {
        $this->nom_ag = $value;
    }

    public function getAdresseAg()
    {
        return $this->adresse_ag;
    }

    public function setAdresseAg($value)
    {
        $this->adresse_ag = $value;
    }

    public function getEmailAg()
    {
        return $this->email_ag;
    }

    public function setEmailAg($value)
    {
        $this->email_ag = $value;
    }

    public function getDescriptionAg()
    {
        return $this->description_ag;
    }

    public function setDescriptionAg($value)
    {
        $this->description_ag = $value;
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
