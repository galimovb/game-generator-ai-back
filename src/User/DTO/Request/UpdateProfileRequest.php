<?php

namespace App\User\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateProfileRequest
{
    public function __construct(
        #[Assert\Length(max: 255)]
        public ?string $name = null,

        #[Assert\Length(max: 255)]
        public ?string $lastName = null,

        #[Assert\Length(max: 255)]
        public ?string $middleName = null,

        #[Assert\Email]
        #[Assert\Length(max: 255)]
        #[Assert\NotBlank]
        public string $email,

        #[Assert\Length(max: 255)]
        #[Assert\Regex(pattern: '/^[a-zA-Z0-9]+$/', message: 'Логин может содержать только буквы, цифры')]
        public ?string $login = null,

        #[Assert\Regex(pattern: '/^data:image\/(jpeg|png|webp);base64,/', message: 'Неверный формат base64 изображения')]
        public ?string $avatar = null,
    ) {
    }
}
