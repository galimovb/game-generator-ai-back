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
    private ?int $minAge = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxAge = null;

    #[ORM\Column(nullable: true)]
    private ?int $minPlayers = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxPlayers = null;

    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    #[ORM\Column(length: 20, nullable: true, enumType: GameLocationType::class)]
    private ?GameLocationType $locationType = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $photos = null;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: GameStage::class, cascade: ['persist', 'remove'])]
    private Collection $stages;

    #[ORM\Column(nullable: true)]
    private ?array $requisites;

    #[ORM\Column]
    private bool $isPublic = false;

    /**
     * @var Collection<int, GameComment>
     */
    #[ORM\OneToMany(targetEntity: GameComment::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, GameLike>
     */
    #[ORM\OneToMany(targetEntity: GameLike::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $likes;

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
    }

    public function getRequisites(): ?array
    {
        return $this->requisites;
    }

    public function setRequisites(?array $requisites): void
    {
        $this->requisites = $requisites;
    }

    public function getStages(): Collection
    {
        return $this->stages;
    }

    public function setStages(Collection $stages): void
    {
        $this->stages = $stages;
    }


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

    public function getMinAge(): ?int
    {
        return $this->minAge;
    }

    public function setMinAge(?int $minAge): static
    {
        $this->minAge = $minAge;
        return $this;
    }

    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    public function setMaxAge(?int $maxAge): static
    {
        $this->maxAge = $maxAge;
        return $this;
    }

    public function getMinPlayers(): ?int
    {
        return $this->minPlayers;
    }

    public function setMinPlayers(?int $minPlayers): static
    {
        $this->minPlayers = $minPlayers;
        return $this;
    }

    public function getMaxPlayers(): ?int
    {
        return $this->maxPlayers;
    }

    public function setMaxPlayers(?int $maxPlayers): static
    {
        $this->maxPlayers = $maxPlayers;
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

    public function setLocationType(?GameLocationType $locationType): void
    {
        $this->locationType = $locationType;
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

    /**
     * @return Collection<int, GameComment>
     */
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
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getGame() === $this) {
                $comment->setGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GameLike>
     */
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
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getGame() === $this) {
                $like->setGame(null);
            }
        }

        return $this;
    }
}