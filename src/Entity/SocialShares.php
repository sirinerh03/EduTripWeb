<?php

namespace App\Entity;

use App\Repository\SocialSharesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SocialSharesRepository::class)]
class SocialShares
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $post_id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $plateforms = null;

    #[ORM\Column]
    private ?\DateTime $scheduled_at = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $metadata = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostId(): ?int
    {
        return $this->post_id;
    }

    public function setPostId(int $post_id): static
    {
        $this->post_id = $post_id;

        return $this;
    }

    public function getPlateforms(): ?string
    {
        return $this->plateforms;
    }

    public function setPlateforms(string $plateforms): static
    {
        $this->plateforms = $plateforms;

        return $this;
    }

    public function getScheduledAt(): ?\DateTime
    {
        return $this->scheduled_at;
    }

    public function setScheduledAt(\DateTime $scheduled_at): static
    {
        $this->scheduled_at = $scheduled_at;

        return $this;
    }

    public function getMetadata(): ?string
    {
        return $this->metadata;
    }

    public function setMetadata(string $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
