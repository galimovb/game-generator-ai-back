<?php

namespace App\DTO\Requests;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateProfileRequest
{
    public function __construct(
        #[Assert\Length(min: 2, max: 255)]
        public readonly ?string $name = null,

        #[Assert\Length(min: 2, max: 255)]
        public readonly ?string $lastName = null,

        #[Assert\Length(min: 2, max: 255)]
        public readonly ?string $middleName = null,

        #[Assert\Email]
        #[Assert\Length(max: 255)]
        public readonly ?string $email = null,

        #[Assert\Length(min: 3, max: 50)]
        #[Assert\Regex(pattern: '/^[a-zA-Z0-9_]+$/', message: 'Логин может содержать только буквы, цифры и подчеркивание')]
        public readonly ?string $login = null,

        #[Assert\Regex(pattern: '/^data:image\/(jpeg|png|webp);base64,/', message: 'Неверный формат base64 изображения')]
        public readonly ?string $avatar = null
    ) {}
}