<?php

namespace App\Stage\Controller;

use App\Shared\DTO\Response\ApiResponse;
use App\Stage\DTO\Request\CreateStageRequest;
use App\Stage\DTO\Request\UpdateStageRequest;
use App\Stage\DTO\Response\StageResponse;
use App\Stage\Entity\Stage;
use App\Stage\Service\StageService;
use App\User\Entity\User;
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
            $stage = $this->stageService->create($request, $user);
            return ApiResponse::success(StageResponse::fromEntity($stage), 201);
    }

    #[Route('/stages/{id}', methods: ['PUT', 'PATCH'])]
    public function update(
        ?Stage $stage = null,
        #[MapRequestPayload] UpdateStageRequest $request,
        #[CurrentUser] User $user
    ): JsonResponse {
            $stage = $this->stageService->update($stage, $request, $user);
            return ApiResponse::success(StageResponse::fromEntity($stage));
    }

    #[Route('/stages/{id}', methods: ['DELETE'])]
    public function delete(
        ?Stage $stage = null,
        #[CurrentUser] User $user
    ): JsonResponse {
            $this->stageService->delete($stage, $user);
            return ApiResponse::success(true, 204);
    }
}