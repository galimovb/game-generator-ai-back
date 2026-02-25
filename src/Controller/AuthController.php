<?php

namespace App\Controller;

use App\DTO\request\RegisterUserRequest;
use App\DTO\response\ApiResponse;
use App\DTO\response\UserResponse;
use App\Service\UserRegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth', name: 'api_auth_')]
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserRegistrationService $registrationService
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegisterUserRequest $request
    ): JsonResponse {
        $user = $this->registrationService->register($request);
        return ApiResponse::success(UserResponse::fromEntity($user), 201);
    }
}