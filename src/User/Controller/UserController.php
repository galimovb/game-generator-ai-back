<?php

namespace App\User\Controller;

use App\Shared\DTO\Response\ApiResponse;
use App\User\DTO\Request\UpdateProfileRequest;
use App\User\DTO\Request\UpdateUserSettingsRequest;
use App\User\DTO\Response\UserResponse;
use App\User\DTO\Response\UserSettingsResponse;
use App\User\Entity\User;
use App\User\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/users', name: 'app_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ApiResponse $apiResponse,
    ) {
    }

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function profile(#[CurrentUser] User $user): JsonResponse
    {
        return $this->apiResponse->success(UserResponse::fromEntity($user));
    }

    #[Route('/profile', name: 'profile_update', methods: ['PUT', 'PATCH'])]
    public function updateProfile(
        #[CurrentUser] User $user,
        #[MapRequestPayload] UpdateProfileRequest $request,
    ): JsonResponse {
        $user = $this->userService->updateProfile($user, $request);

        return $this->apiResponse->success(UserResponse::fromEntity($user));
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $user = $this->userService->getUser($id);

        return $this->apiResponse->success(UserResponse::fromEntity($user));
    }

    #[Route('/profile/settings', name: 'profile_settings_get', methods: ['GET'])]
    public function getSettings(#[CurrentUser] User $user): JsonResponse
    {
        $settings = $this->userService->getSettings($user);

        return $this->apiResponse->success(UserSettingsResponse::fromEntity($settings));
    }

    #[Route('/profile/settings', name: 'profile_settings_update', methods: ['PUT', 'PATCH'])]
    public function updateSettings(
        #[CurrentUser] User $user,
        #[MapRequestPayload] UpdateUserSettingsRequest $request,
    ): JsonResponse {
        $settings = $this->userService->updateSettings($user, $request);

        return $this->apiResponse->success(UserSettingsResponse::fromEntity($settings));
    }
}
