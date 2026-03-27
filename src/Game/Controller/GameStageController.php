<?php

namespace App\Game\Controller;

use App\Game\DTO\Request\CreateStageRequest;
use App\Game\DTO\Request\UpdateStageRequest;
use App\Game\DTO\Response\GameStageResponse;
use App\Game\Entity\GameStage;
use App\Game\Service\GameStageService;
use App\Shared\DTO\Response\ApiResponse;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/games/{gameId}/stages', name: 'app_stages_')]
class GameStageController extends AbstractController
{
    public function __construct(
        private readonly GameStageService $stageService
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(
        int $gameId,
        #[MapRequestPayload] CreateStageRequest $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        $stage = $this->stageService->create($gameId, $request, $user);
        return ApiResponse::success(GameStageResponse::fromEntity($stage), 201);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(
        int $gameId,
        ?GameStage $stage = null,
        #[MapRequestPayload] UpdateStageRequest $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        $stage = $this->stageService->update($gameId, $stage, $request, $user);
        return ApiResponse::success(GameStageResponse::fromEntity($stage));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(
        int $gameId,
        ?GameStage $stage = null,
        #[CurrentUser] User $user
    ): JsonResponse {
        $this->stageService->delete($gameId, $stage, $user);
        return ApiResponse::success(true, 204);
    }
}