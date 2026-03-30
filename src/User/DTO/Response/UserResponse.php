<?php

namespace App\User\DTO\Response;

use App\User\Entity\User;

readonly class UserResponse
{
    public function __construct(
        public int $id,
        public ?string $name,
        public ?string $lastName,
        public ?string $middleName,
        public string $email,
        public ?string $login,
        public ?string $avatar,
        public array $roles,
        public bool $isActive,
        public bool $isBlocked,
        public bool $isVerified,
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId(),
            name: $user->getName(),
            lastName: $user->getLastName(),
            middleName: $user->getMiddleName(),
            email: $user->getEmail(),
            login: $user->getLogin(),
            avatar: $user->getAvatar(),
            roles: $user->getRoles(),
            isActive: $user->isActive(),
            isBlocked: $user->isBlocked(),
            isVerified: $user->isVerified(),
        );
    }
}