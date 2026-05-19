<?php

namespace App\Game\DTO\Request;

use App\Shared\Enum\GameActivityLevel;
use App\Shared\Enum\GameLocationType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

readonly class GenerateGameRequest
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Range(min: 3, max: 25)]
        public int $age,

        #[Assert\NotNull]
        #[Assert\Range(min: 1, max: 50)]
        public int $players,

        #[Assert\NotNull]
        #[Assert\Range(min: 5, max: 120)]
        public int $duration,

        #[Assert\NotNull]
        #[Assert\Choice(callback: [GameLocationType::class, 'values'])]
        public string $locationType,

        #[Assert\NotNull]
        #[Assert\Range(min: 1, max: 1000)]
        public int $fieldWidth,

        #[Assert\NotNull]
        #[Assert\Range(min: 1, max: 1000)]
        public int $fieldLength,

        #[Assert\NotNull]
        #[Assert\Choice(callback: [GameActivityLevel::class, 'values'])]
        public string $activityLevel,

        #[Assert\All([
            new Assert\Type('string'),
            new Assert\NotBlank(),
            new Assert\Length(max: 100),
        ])]
        public ?array $requisites = null,

        #[Assert\All([
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\Regex('/^data:image\/(jpeg|png|webp|gif);base64,/'),
        ])]
        public array $photos = [],

        public ?string $locationDescription = null,
    ) {
    }

    #[Assert\Callback]
    public function validateLocationContext(ExecutionContextInterface $context): void
    {
        if (empty($this->photos) && (empty($this->locationDescription) || '' === trim($this->locationDescription))) {
            $context->buildViolation('Когда фото отсутствуют, обязательно укажите описание местности (locationDescription)')
                ->atPath('locationDescription')
                ->addViolation();
        }
    }
}
