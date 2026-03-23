<?php

namespace App\Entity;

use App\Enum\ModelType;
use App\Repository\UserSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserSettingsRepository::class)]
class UserSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: ModelType::class)]
    private ModelType $generationModel = ModelType::QWEN3_VL_8B;

    #[ORM\Column]
    private float $generationCreative = 0.7;

    #[ORM\OneToOne(inversedBy: 'userSettings', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGenerationModel(): ?ModelType
    {
        return $this->generationModel;
    }

    public function setGenerationModel(ModelType $generationModel): static
    {
        $this->generationModel = $generationModel;

        return $this;
    }

    public function getGenerationCreative(): ?float
    {
        return $this->generationCreative;
    }

    public function setGenerationCreative(float $generationCreative): static
    {
        $this->generationCreative = $generationCreative;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
