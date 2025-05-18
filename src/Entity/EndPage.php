<?php

namespace App\Entity;

use App\Repository\EndPageRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EndPageRepository::class)]
class EndPage
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[Groups(["endpage:view", "endpage:solo"])]
    private ?Uuid $id;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["endpage:view", "endpage:solo"])]
    private ?string $category = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["endpage:view", "endpage:solo"])]
    private ?string $text = null;
    #[ORM\Column]
    #[Groups(["endpage:view", "endpage:solo"])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::TEXT, nullable:  true)]
    #[Groups(["endpage:view", "endpage:solo"])]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["endpage:view", "endpage:solo"])]
    private ?string $music = "";

    #[ORM\Column(length: 255)]
    #[Groups(["endpage:view", "endpage:solo"])]
    private ?string $background = null;

    #[ORM\Column(type: Types::TEXT, nullable:  true)]
    #[Groups(["endpage:view", "endpage:solo"])]
    private ?string $gif = null;

    #[ORM\ManyToOne(inversedBy: 'endPages')]
    #[Groups(["endpage:view"])]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    #[Groups(["endpage:view", "endpage:solo"])]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups(["endpage:view", "endpage:solo"])]
    private ?int $views = 0;

    #[ORM\Column]
    #[Groups(["endpage:view", "endpage:solo"])]
    private ?int $likes = 0;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->createdAt = new DateTimeImmutable("now");
    }
    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getMusic(): ?string
    {
        return $this->music;
    }

    public function setMusic(?string $music): static
    {
        $this->music = $music;

        return $this;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function setBackground(string $background): static
    {
        $this->background = $background;

        return $this;
    }

    public function getGif(): ?string
    {
        return $this->gif;
    }

    public function setGif(?string $gif): static
    {
        $this->gif = $gif;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): static
    {
        $this->views = $views;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): static
    {
        $this->likes = $likes;

        return $this;
    }
}
