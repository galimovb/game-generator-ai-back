<?php

namespace App\DTO\Requests;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterUserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        public readonly string $password,

        #[Assert\Length(max: 255)]
        public readonly ?string $login = null,

        #[Assert\Length(max: 255)]
        public readonly ?string $name = null,

        #[Assert\Length(max: 255)]
        public readonly ?string $lastName = null,

        #[Assert\Length(max: 255)]
        public readonly ?string $middleName = null,
    ) {}
}