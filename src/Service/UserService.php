<?php

namespace App\Service;

use App\DTO\request\UpdateProfileRequest;
use App\Entity\User;
use App\Enum\ErrorCode;
use App\Exception\ApiException;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function updateProfile(User $user, UpdateProfileRequest $request): User
    {
        if ($request->name !== null) {
            $user->setName($request->name);
        }

        if ($request->lastName !== null) {
            $user->setLastName($request->lastName);
        }

        if ($request->middleName !== null) {
            $user->setMiddleName($request->middleName);
        }

        if ($request->email !== null) {
            $this->checkEmailUnique($request->email, $user->getId());
            $user->setEmail($request->email);
        }

        if ($request->login !== null) {
            $this->checkLoginUnique($request->login, $user->getId());
            $user->setLogin($request->login);
        }

        if ($request->avatar !== null) {
            $user->setAvatar($request->avatar);
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($user);

        return $user;
    }

    public function getUser(int $id): User
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw new ApiException(ErrorCode::NOT_FOUND);
        }

        return $user;
    }

    public function getUsers(int $page, int $limit): array
    {
        $repository = $this->entityManager->getRepository(User::class);

        $items = $repository->findBy(
            ['isBlocked' => false],
            ['id' => 'DESC'],
            $limit,
            ($page - 1) * $limit
        );

        $total = $repository->count(['isBlocked' => false]);

        return [
            'items' => $items,
            'total' => $total
        ];
    }

    public function blockUser(int $id, User $admin): void
    {
        if (!in_array('ROLE_ADMIN', $admin->getRoles())) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        $user = $this->getUser($id);

        if ($user->getId() === $admin->getId()) {
            throw new ApiException(ErrorCode::CANNOT_BLOCK_SELF);
        }

        $user->setIsBlocked(true);
        $this->entityManager->flush();
    }

    public function unblockUser(int $id, User $admin): void
    {
        if (!in_array('ROLE_ADMIN', $admin->getRoles())) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        $user = $this->getUser($id);
        $user->setIsBlocked(false);
        $this->entityManager->flush();
    }

    private function checkEmailUnique(string $email, int $currentUserId): void
    {
        $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($existing && $existing->getId() !== $currentUserId) {
            throw new ApiException(ErrorCode::EMAIL_EXIST);
        }
    }

    private function checkLoginUnique(?string $login, int $currentUserId): void
    {
        if (!$login) {
            return;
        }

        $existing = $this->entityManager->getRepository(User::class)->findOneBy(['login' => $login]);

        if ($existing && $existing->getId() !== $currentUserId) {
            throw new ApiException(ErrorCode::LOGIN_EXIST);
        }
    }
}