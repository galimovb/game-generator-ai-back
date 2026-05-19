<?php

namespace App\Game\DTO\Request;

use App\Shared\Enum\GameActivityLevel;
use App\Shared\Enum\GameLocationType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

readonly class UpdateGameRequest
{
    public function __construct(
        #[Assert\Length(min: 3, max: 255)]
        public ?string $title = null,

        #[Assert\Length(min: 10)]
        public ?string $description = null,

        #[Assert\Range(min: 3, max: 25)]
        public ?int $age = null,

        #[Assert\Range(min: 1, max: 50)]
        public ?int $players = null,

        #[Assert\Positive]
        #[Assert\Range(min: 5, max: 120)]
        public ?int $duration = null,

        #[Assert\Choice(callback: [GameLocationType::class, 'values'])]
        public ?string $locationType = null,

        #[Assert\Range(min: 1, max: 1000)]
        public ?int $fieldWidth = null,

        #[Assert\Range(min: 1, max: 1000)]
        public ?int $fieldLength = null,

        #[Assert\Choice(callback: [GameActivityLevel::class, 'values'])]
        public ?string $activityLevel = null,

        #[Assert\Type('array')]
        public ?array $requisites = null,

        #[Assert\Type('boolean')]
        public ?bool $isPublic = null,

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
