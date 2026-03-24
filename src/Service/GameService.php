<?php

namespace App\Service;

use App\DTO\Requests\UpdateGameRequest;
use App\Entity\Game;
use App\Entity\User;
use App\Enum\ErrorCode;
use App\Enum\GameLocationType;
use App\Exception\ApiException;
use App\Repository\GameLikeRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;

class GameService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GameRepository $gameRepository,
        private readonly GameLikeRepository $gameLikeRepository,
    ) {}

    private function attachLikeInfo(array $games, ?User $user): array
    {
        if (!$user || empty($games)) {
            return array_map(fn($game) => [
                'game' => $game,
                'isLiked' => false,
            ], $games);
        }

        $gameIds = array_map(fn($game) => $game->getId(), $games);

        $likedIds = $this->gameLikeRepository->findLikedGameIds($user, $gameIds);
        $likedMap = array_flip($likedIds);

        return array_map(function ($game) use ($likedMap) {
            return [
                'game' => $game,
                'isLiked' => isset($likedMap[$game->getId()]),
            ];
        }, $games);
    }

    // ==================== ПУБЛИЧНЫЕ МЕТОДЫ ====================

    public function getPublicGames(int $page, int $limit, ?User $user = null): array
    {
        $items = $this->gameRepository->findBy(
            ['isPublic' => true],
            ['createdAt' => 'DESC'],
            $limit,
            ($page - 1) * $limit
        );

        $total = $this->gameRepository->count(['isPublic' => true]);

        return [
            'items' => $this->attachLikeInfo($items, $user),
            'total' => $total
        ];
    }

    public function getUserLikeGames(User $user, int $page, int $limit): array
    {
        $likes = $this->gameLikeRepository->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC'],
            $limit,
            ($page - 1) * $limit
        );

        $total = $this->gameLikeRepository->count(['author' => $user]);

        $games = array_map(fn($like) => $like->getGame(), $likes);

        return [
            'items' => array_map(fn($game) => [
                'game' => $game,
                'isLiked' => true,
            ], $games),
            'total' => $total
        ];
    }

    public function getUserGames(User $user, int $page, int $limit): array
    {
        $items = $this->gameRepository->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC'],
            $limit,
            ($page - 1) * $limit
        );

        $total = $this->gameRepository->count(['author' => $user]);

        return [
            'items' => $this->attachLikeInfo($items, $user),
            'total' => $total
        ];
    }

    /**
     * @throws \Exception
     */
    public function getGame(int $id, ?User $user = null): Game
    {
        $game = $this->findGameOrFail($id);

        if (!$game->isPublic()) {
            $this->checkAccess($game, $user);
        }

        return $game;
    }

    public function updateGame(int $id, UpdateGameRequest $request, User $user): Game
    {
        $game = $this->findGameOrFail($id);
        $this->checkAccess($game, $user);

        if ($request->title !== null) {
            $game->setTitle($request->title);
        }
        if ($request->description !== null) {
            $game->setDescription($request->description);
        }
        if ($request->minAge !== null) {
            $game->setMinAge($request->minAge);
        }
        if ($request->maxAge !== null) {
            $game->setMaxAge($request->maxAge);
        }
        if ($request->minPlayers !== null) {
            $game->setMinPlayers($request->minPlayers);
        }
        if ($request->maxPlayers !== null) {
            $game->setMaxPlayers($request->maxPlayers);
        }
        if ($request->duration !== null) {
            $game->setDuration($request->duration);
        }
        if ($request->locationType !== null) {
            $game->setLocationType(GameLocationType::from($request->locationType));
        }
        if ($request->requisites !== null) {
            $game->setRequisites($request->requisites);
        }
        if ($request->isPublic !== null) {
            $game->setIsPublic($request->isPublic);
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($game);

        return $game;
    }

    public function deleteGame(int $id, User $user): void
    {
        $game = $this->findGameOrFail($id);
        $this->checkAccess($game, $user);

        $this->entityManager->remove($game);
        $this->entityManager->flush();
    }

    // ==================== PRIVATE МЕТОДЫ ====================

    private function findGameOrFail(int $id): Game
    {
        $game = $this->gameRepository->find($id);
        if (!$game) {
            throw new ApiException(ErrorCode::GAME_NOT_FOUND);
        }
        return $game;
    }

    private function checkAccess(Game $game, User $user): void
    {
        if (!$user->isAdmin() && !$user->isGameAuthor($game)) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }
    }
}