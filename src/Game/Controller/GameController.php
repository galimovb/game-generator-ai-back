<?php

namespace App\Game\Controller;

use App\Game\DTO\Request\GameListFilters;
use App\Game\DTO\Request\GenerateGameRequest;
use App\Game\DTO\Request\UpdateGameRequest;
use App\Game\DTO\Response\GameResponse;
use App\Game\Service\GameGenerationService;
use App\Game\Service\GameService;
use App\Shared\DTO\Response\ApiResponse;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/games', name: 'app_games_')]
class GameController extends AbstractController
{
    public function __construct(
        private readonly GameService $gameService,
        private readonly GameGenerationService $gameGenerationService,
        private readonly ApiResponse $apiResponse,
    ) {
    }

    #[Route('/generate', name: 'generate', methods: ['POST'])]
    public function generate(
        #[MapRequestPayload] GenerateGameRequest $request,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $game = $this->gameGenerationService->generateAndSave($request, $user);

        return $this->apiResponse->success(GameResponse::fromEntity($game), 201);
    }

    #[Route('', name: 'public', methods: ['GET'])]
    public function listPublic(
        #[MapQueryString] GameListFilters $filters,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $result = $this->gameService->getPublicGames($filters, $user);

        return $this->apiResponse->success([
            'items' => array_map(
                fn ($item) => GameResponse::fromEntity($item['game'], $item['isLiked']),
                $result['items']
            ),
            'pagination' => [
                'page' => $filters->page,
                'limit' => $filters->limit,
                'total' => $result['total'],
            ],
        ]);
    }

    #[Route('/liked', name: 'liked', methods: ['GET'])]
    public function listLike(
        Request $request,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);

        $result = $this->gameService->getUserLikeGames($user, $page, $limit);

        return $this->apiResponse->success([
            'items' => array_map(
                fn ($item) => GameResponse::fromEntity($item['game'], $item['isLiked']),
                $result['items']
            ),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total'],
            ],
        ]);
    }

    #[Route('/my', name: 'my', methods: ['GET'])]
    public function listMy(
        Request $request,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);

        $result = $this->gameService->getUserGames($user, $page, $limit);

        return $this->apiResponse->success([
            'items' => array_map(
                fn ($item) => GameResponse::fromEntity($item['game'], $item['isLiked']),
                $result['items']
            ),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total'],
            ],
        ]);
    }

    #[Route('/popular', name: 'popular', methods: ['GET'])]
    public function listPopular(
        Request $request,
        #[CurrentUser] ?User $user,
    ): JsonResponse {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);

        $result = $this->gameService->getTopLikedGames($page, $limit, $user);

        return $this->apiResponse->success([
            'items' => array_map(
                fn ($item) => GameResponse::fromEntity($item['game'], $item['isLiked']),
                $result['items']
            ),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total'],
            ],
        ]);
    }

    #[Route('/{id}', name: 'details', methods: ['GET'])]
    public function get(
        int $id,
        #[CurrentUser] ?User $user,
    ): JsonResponse {
        $game = $this->gameService->getGame($id);

        return $this->apiResponse->success(GameResponse::fromEntity($game));
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(
        int $id,
        #[MapRequestPayload] UpdateGameRequest $request,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $game = $this->gameService->updateGame($id, $request, $user);

        return $this->apiResponse->success(GameResponse::fromEntity($game));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $this->gameService->deleteGame($id, $user);

        return $this->apiResponse->success(true, 204);
    }
}
