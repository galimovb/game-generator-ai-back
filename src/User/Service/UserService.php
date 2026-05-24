<?php

namespace App\User\Service;

use App\Shared\Enum\ErrorCode;
use App\Shared\Enum\ModelType;
use App\Shared\Enum\UploadType;
use App\Shared\Exception\ApiException;
use App\Shared\Service\UploadService;
use App\User\DTO\Request\UpdateProfileRequest;
use App\User\DTO\Request\UpdateUserSettingsRequest;
use App\User\Entity\User;
use App\User\Entity\UserSettings;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UploadService $uploadService,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function updateProfile(User $user, UpdateProfileRequest $request): User
    {
        if (null !== $request->name) {
            $user->setName($request->name);
        }

        if (null !== $request->lastName) {
            $user->setLastName($request->lastName);
        }

        if (null !== $request->middleName) {
            $user->setMiddleName($request->middleName);
        }

        if (null !== $request->email && $request->email !== $user->getEmail()) {
            $this->checkEmailUnique($request->email);
            $user->setEmail($request->email);
        }

        if (null !== $request->login && $request->login !== $user->getLogin()) {
            $this->checkLoginUnique($request->login);
            $user->setLogin($request->login);
        }

        if (null !== $request->avatar) {
            // Удаляем старый
            if ($user->getAvatar()) {
                $this->uploadService->deleteFile($user->getAvatar());
            }

            // Загружаем новый и получаем путь
            $avatarPath = $this->uploadService->uploadFromBase64(
                base64: $request->avatar,
                type: UploadType::AVATAR,
                entityId: $user->getId()
            );

            // Сохраняем путь
            $user->setAvatar($avatarPath);
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($user);

        return $user;
    }

    public function getUser(int $id): User
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new ApiException(ErrorCode::USER_NOT_FOUND);
        }

        return $user;
    }

    public function checkEmailUnique(string $email): void
    {
        $existing = $this->userRepository->findOneBy(['email' => $email]);

        if ($existing) {
            throw new ApiException(ErrorCode::EMAIL_EXIST);
        }
    }

    public function checkLoginUnique(?string $login): void
    {
        if (!$login) {
            return;
        }

        $existing = $this->userRepository->findOneBy(['login' => $login]);

        if ($existing) {
            throw new ApiException(ErrorCode::LOGIN_EXIST);
        }
    }

    public function getSettings(User $user): UserSettings
    {
        return $user->getUserSettings();
    }

    public function updateSettings(User $user, UpdateUserSettingsRequest $request): UserSettings
    {
        $settings = $this->getSettings($user);

        $settings->setGenerationModel(ModelType::from($request->generationModel));
        $settings->setGenerationCreative($request->generationCreative);

        $this->entityManager->flush();

        return $settings;
    }
}
