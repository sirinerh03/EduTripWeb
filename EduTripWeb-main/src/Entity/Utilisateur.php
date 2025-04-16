<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Candidature;

#[ORM\Entity]
class Utilisateur
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 50)]
    private string $nom;

    #[ORM\Column(type: "string", length: 50)]
    private string $prenom;

    #[ORM\Column(type: "string", length: 100)]
    private string $mail;

    #[ORM\Column(type: "string", length: 20)]
    private string $tel;

    #[ORM\Column(type: "string", length: 255)]
    private string $password;

    #[ORM\Column(type: "string", length: 255)]
    private string $confirm_password;

    #[ORM\Column(type: "string", length: 20)]
    private string $status;

    #[ORM\Column(type: "string", length: 50)]
    private string $role;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($value)
    {
        $this->nom = $value;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($value)
    {
        $this->prenom = $value;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setMail($value)
    {
        $this->mail = $value;
    }

    public function getTel()
    {
        return $this->tel;
    }

    public function setTel($value)
    {
        $this->tel = $value;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function getConfirm_password()
    {
        return $this->confirm_password;
    }

    public function setConfirm_password($value)
    {
        $this->confirm_password = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($value)
    {
        $this->role = $value;
    }

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Candidature::class)]
    private Collection $candidatures;

        public function getCandidatures(): Collection
        {
            return $this->candidatures;
        }
    
        public function addCandidature(Candidature $candidature): self
        {
            if (!$this->candidatures->contains($candidature)) {
                $this->candidatures[] = $candidature;
                $candidature->setUser($this);
            }
    
            return $this;
        }
    
        public function removeCandidature(Candidature $candidature): self
        {
            if ($this->candidatures->removeElement($candidature)) {
                // set the owning side to null (unless already changed)
                if ($candidature->getUser() === $this) {
                    $candidature->setUser(null);
                }
            }
    
            return $this;
        }
}
