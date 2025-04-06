<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\PostContentOrImage;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[Vich\Uploadable]
#[PostContentOrImage]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_post = null;


    #[ORM\Column(length: 255)]

    #[Assert\Length(
      
        max: 255,
    
        maxMessage: 'Le contenu ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $contenu = null;



    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'post_images', fileNameProperty: 'image')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255)]
    private ?string $categorie = null;

    #[ORM\Column(nullable: true)]
    private ?int $likes = null;

    #[ORM\Column(nullable: true)]
    private ?int $dislikes = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(name: "id_etudiant", referencedColumnName: "id_etudiant", nullable: true)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'post')]
private Collection $commentaires;

public function __construct()
{
    $this->commentaires = new ArrayCollection();
}

public function getCommentaires(): Collection
{
    return $this->commentaires;
}

    public function getIdPost(): ?int
    {
        return $this->id_post;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;
        return $this;
    }

 

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // Met à jour la date de modification seulement si un fichier est uploadé
            $this->date_creation = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getImagePath(): string
    {
        return 'uploads/posts/'.$this->image;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(?int $likes): static
    {
        $this->likes = $likes;
        return $this;
    }

    public function getDislikes(): ?int
    {
        return $this->dislikes;
    }

    public function setDislikes(?int $dislikes): static
    {
        $this->dislikes = $dislikes;
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }
}