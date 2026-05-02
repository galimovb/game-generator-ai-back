<?php

namespace App\Game\Entity;

use App\Game\Repository\GameRepository;
use App\Shared\Enum\GameLocationType;
use App\Shared\Trait\AuthorableTrait;
use App\Shared\Trait\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Game
{
    use TimestampableTrait;
    use AuthorableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column(nullable: true)]
    private ?int $players = null;

    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    #[ORM\Column(length: 20, nullable: true, enumType: GameLocationType::class)]
    private ?GameLocationType $locationType = null;

    #[ORM\Column(nullable: true)]
    private ?int $fieldWidth = null;

    #[ORM\Column(nullable: true)]
    private ?int $fieldLength = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $activityLevel = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $photos = null;

    #[ORM\Column(nullable: true)]
    private ?array $requisites = null;

    #[ORM\Column]
    private bool $isPublic = false;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: GameStage::class, cascade: ['persist', 'remove'])]
    private Collection $stages;

    #[ORM\OneToMany(targetEntity: GameComment::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $comments;

    #[ORM\OneToMany(targetEntity: GameLike::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $likes;

    public function __construct()
    {
        $this->stages = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): static
    {
        $this->age = $age;
        return $this;
    }

    public function getPlayers(): ?int
    {
        return $this->players;
    }

    public function setPlayers(?int $players): static
    {
        $this->players = $players;
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    public function getLocationType(): ?GameLocationType
    {
        return $this->locationType;
    }

    public function setLocationType(?GameLocationType $locationType): static
    {
        $this->locationType = $locationType;
        return $this;
    }

    public function getFieldWidth(): ?int
    {
        return $this->fieldWidth;
    }

    public function setFieldWidth(?int $fieldWidth): static
    {
        $this->fieldWidth = $fieldWidth;
        return $this;
    }

    public function getFieldLength(): ?int
    {
        return $this->fieldLength;
    }

    public function setFieldLength(?int $fieldLength): static
    {
        $this->fieldLength = $fieldLength;
        return $this;
    }

    public function getActivityLevel(): ?string
    {
        return $this->activityLevel;
    }

    public function setActivityLevel(?string $activityLevel): static
    {
        $this->activityLevel = $activityLevel;
        return $this;
    }

    public function getPhotos(): ?array
    {
        return $this->photos;
    }

    public function setPhotos(?array $photos): static
    {
        $this->photos = $photos;
        return $this;
    }

    public function getRequisites(): ?array
    {
        return $this->requisites;
    }

    public function setRequisites(?array $requisites): static
    {
        $this->requisites = $requisites;
        return $this;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    public function getStages(): Collection
    {
        return $this->stages;
    }

    public function setStages(Collection $stages): static
    {
        $this->stages = $stages;
        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(GameComment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setGame($this);
        }
        return $this;
    }

    public function removeComment(GameComment $comment): static
    {
        if ($this->comments->removeElement($comment) && $comment->getGame() === $this) {
            $comment->setGame(null);
        }
        return $this;
    }

    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(GameLike $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setGame($this);
        }
        return $this;
    }

    public function removeLike(GameLike $like): static
    {
        if ($this->likes->removeElement($like) && $like->getGame() === $this) {
            $like->setGame(null);
        }
        return $this;
    }
}