<?php

namespace App\Support\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

readonly class CreateTicketMessageRequest
{
    public function __construct(
        #[Assert\Length(max: 5000)]
        public string $text,

        #[Assert\All([
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\Regex('/^data:image\/(jpeg|png|webp|gif);base64,/'),
        ])]
        public array $photos = [],
    ) {
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if (empty($this->text) && empty($this->photos)) {
            $context->buildViolation('Необходимо указать текст сообщения или прикрепить фото')
                ->atPath('text')
                ->addViolation();
        }
    }
}
