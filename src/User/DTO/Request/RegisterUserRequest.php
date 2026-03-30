<?php

namespace App\User\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class RegisterUserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        public string $password,

        #[Assert\Length(max: 255)]
        public ?string $login = null,

        #[Assert\Length(max: 255)]
        public ?string $name = null,

        #[Assert\Length(max: 255)]
        public ?string $lastName = null,

        #[Assert\Length(max: 255)]
        public ?string $middleName = null,
    ) {}
}