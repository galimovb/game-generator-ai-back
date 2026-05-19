<?php

namespace App\Game\Entity;

use App\Game\Repository\GameJudgeLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameJudgeLogRepository::class)]
#[ORM\HasLifecycleCallbacks]
class GameJudgeLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Game::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Game $game;

    #[ORM\Column]
    private float $score;

    #[ORM\Column]
    private bool $passed;

    #[ORM\Column]
    private bool $isSafe;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $criteria = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $safetyIssues = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $failReason = null;

    #[ORM\Column]
    private int $attempt = 1;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getGame(): Game { return $this->game; }
    public function setGame(Game $game): static { $this->game = $game; return $this; }

    public function getScore(): float { return $this->score; }
    public function setScore(float $score): static { $this->score = $score; return $this; }

    public function isPassed(): bool { return $this->passed; }
    public function setPassed(bool $passed): static { $this->passed = $passed; return $this; }

    public function isSafe(): bool { return $this->isSafe; }
    public function setIsSafe(bool $isSafe): static { $this->isSafe = $isSafe; return $this; }

    public function getCriteria(): ?array { return $this->criteria; }
    public function setCriteria(?array $criteria): static { $this->criteria = $criteria; return $this; }

    public function getSafetyIssues(): ?array { return $this->safetyIssues; }
    public function setSafetyIssues(?array $safetyIssues): static { $this->safetyIssues = $safetyIssues; return $this; }

    public function getFailReason(): ?string { return $this->failReason; }
    public function setFailReason(?string $failReason): static { $this->failReason = $failReason; return $this; }

    public function getAttempt(): int { return $this->attempt; }
    public function setAttempt(int $attempt): static { $this->attempt = $attempt; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
