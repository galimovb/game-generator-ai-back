<?php

namespace App\User\Controller;

use App\Shared\DTO\Response\ApiResponse;
use App\User\DTO\Request\RegisterUserRequest;
use App\User\DTO\Response\UserResponse;
use App\User\Service\UserRegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth', name: 'api_auth_')]
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserRegistrationService $registrationService,
        private readonly ApiResponse $apiResponse,
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegisterUserRequest $request,
    ): JsonResponse {
        $user = $this->registrationService->register($request);

        return $this->apiResponse->success(UserResponse::fromEntity($user), 201);
    }
}
