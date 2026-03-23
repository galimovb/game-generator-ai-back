<?php

namespace App\Controller;

use App\DTO\Requests\UpdateProfileRequest;
use App\DTO\Requests\UpdateUserSettingsRequest;
use App\DTO\Responses\ApiResponse;
use App\DTO\Responses\UserResponse;
use App\DTO\Responses\UserSettingsResponse;
use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/users', name: 'app_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function profile(#[CurrentUser] User $user): JsonResponse
    {
            return ApiResponse::success(UserResponse::fromEntity($user));
    }

    #[Route('/profile', name: 'profile_update', methods: ['PUT', 'PATCH'])]
    public function updateProfile(
        #[CurrentUser] User $user,
        #[MapRequestPayload] UpdateProfileRequest $request
    ): JsonResponse {
            $user = $this->userService->updateProfile($user, $request);
            return ApiResponse::success(UserResponse::fromEntity($user));
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
            $user = $this->userService->getUser($id);
            return ApiResponse::success(UserResponse::fromEntity($user));
    }

    #[Route('/profile/settings', name: 'profile_settings_get', methods: ['GET'])]
    public function getSettings(#[CurrentUser] User $user): JsonResponse
    {
        $settings = $this->userService->getSettings($user);

        return ApiResponse::success(UserSettingsResponse::fromEntity($settings));
    }

    #[Route('/profile/settings', name: 'profile_settings_update', methods: ['PUT', 'PATCH'])]
    public function updateSettings(
        #[CurrentUser] User $user,
        #[MapRequestPayload] UpdateUserSettingsRequest $request
    ): JsonResponse
    {
        $settings = $this->userService->updateSettings($user, $request);

        return ApiResponse::success(UserSettingsResponse::fromEntity($settings));
    }
}