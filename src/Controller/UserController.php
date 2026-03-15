<?php

namespace App\Controller;

use App\DTO\Requests\UpdateProfileRequest;
use App\DTO\Responses\ApiResponse;
use App\DTO\Responses\UserResponse;
use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $result = $this->userService->getUsers($page, $limit);

            return ApiResponse::success([
                'items' => array_map(
                    fn($user) => UserResponse::fromEntity($user),
                    $result['items']
                ),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $result['total']
                ]
            ]);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
            $user = $this->userService->getUser($id);
            return ApiResponse::success(UserResponse::fromEntity($user));
    }
}