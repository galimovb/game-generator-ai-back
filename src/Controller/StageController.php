<?php

namespace App\Controller;

use App\DTO\request\CreateStageRequest;
use App\DTO\request\UpdateStageRequest;
use App\DTO\response\ApiResponse;
use App\DTO\response\StageResponse;
use App\Entity\Stage;
use App\Entity\User;
use App\Service\StageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api')]
class StageController extends AbstractController
{
    public function __construct(
        private readonly StageService $stageService
    ) {}

    #[Route('/stages', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateStageRequest $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            $stage = $this->stageService->create($request, $user);
            return ApiResponse::success(StageResponse::fromEntity($stage), 201);
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    #[Route('/stages/{id}', methods: ['PUT', 'PATCH'])]
    public function update(
        ?Stage $stage = null,
        #[MapRequestPayload] UpdateStageRequest $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            $stage = $this->stageService->update($stage, $request, $user);
            return ApiResponse::success(StageResponse::fromEntity($stage));
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    #[Route('/stages/{id}', methods: ['DELETE'])]
    public function delete(
        ?Stage $stage = null,
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            $this->stageService->delete($stage, $user);
            return ApiResponse::success(true, 204);
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }
}