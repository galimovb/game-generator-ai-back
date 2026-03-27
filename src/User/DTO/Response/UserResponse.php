<?php

namespace App\User\DTO\Response;

use App\User\Entity\User;

class UserResponse
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $name,
        public readonly ?string $lastName,
        public readonly ?string $middleName,
        public readonly string $email,
        public readonly ?string $login,
        public readonly ?string $avatar,
        public readonly array $roles,
        public readonly bool $isActive,
        public readonly bool $isBlocked,
        public readonly bool $isVerified,
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