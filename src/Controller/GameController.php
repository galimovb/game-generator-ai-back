<?php

namespace App\Controller;

use App\DTO\request\GenerateGameRequest;
use App\DTO\request\UpdateGameRequest;
use App\DTO\response\ApiResponse;
use App\DTO\response\GameResponse;
use App\Entity\User;
use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/games', name: 'app_games_')]
class GameController extends AbstractController
{
    public function __construct(
        private readonly GameService $gameService
    ) {}

    #[Route('/generate', name: 'generate', methods: ['POST'])]
    public function generate(
        #[MapRequestPayload] GenerateGameRequest $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            $game = $this->gameService->generateAndSave($request, $user);
            return ApiResponse::success(GameResponse::fromEntity($game), 201);
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    #[Route('', name: 'public', methods: ['GET'])]
    public function listPublic(Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $result = $this->gameService->getPublicGames($page, $limit);

            return ApiResponse::success([
                'items' => array_map(
                    fn($game) => GameResponse::fromEntity($game),
                    $result['items']
                ),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $result['total']
                ]
            ]);
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    #[Route('/my', name: 'my', methods: ['GET'])]
    public function listMy(
        Request $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $result = $this->gameService->getUserGames($user, $page, $limit);

            return ApiResponse::success([
                'items' => array_map(
                    fn($game) => GameResponse::fromEntity($game),
                    $result['items']
                ),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $result['total']
                ]
            ]);
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    #[Route('/{id}', name: 'details', methods: ['GET'])]
    public function get(
        int $id,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        try {
            $game = $this->gameService->getGame($id, $user);
            return ApiResponse::success(GameResponse::fromEntity($game));
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(
        int $id,
        #[MapRequestPayload] UpdateGameRequest $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            $game = $this->gameService->updateGame($id, $request, $user);
            return ApiResponse::success(GameResponse::fromEntity($game));
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            $this->gameService->deleteGame($id, $user);
            return ApiResponse::success(true, 204);
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }
}