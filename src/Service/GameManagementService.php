<?php

namespace App\Service;

use App\Contracts\Service\GameLikeServiceInterface;
use App\Contracts\Service\GameManagementServiceInterface;
use App\DTO\Requests\UpdateGameRequest;
use App\Entity\Game;
use App\Entity\User;
use App\Enum\ErrorCode;
use App\Enum\GameLocationType;
use App\Exception\ApiException;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Сервис управления играми (CRUD операции)
 */
class GameManagementService implements GameManagementServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GameRepository $gameRepository,
        private readonly GameLikeServiceInterface $gameLikeService,
    ) {}

    /**
     * Получить игру по ID
     */
    public function getGame(int $id, ?User $user = null): Game
    {
        $game = $this->findGameOrFail($id);

        if (!$game->isPublic()) {
            $this->checkAccess($game, $user);
        }

        return $game;
    }

    /**
     * Получить публичные игры с пагинацией
     * 
     * @return array{items: array, total: int}
     */
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
            'items' => $this->gameLikeService->attachLikeInfo($items, $user),
            'total' => $total
        ];
    }

    /**
     * Получить игры пользователя с пагинацией
     * 
     * @return array{items: array, total: int}
     */
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
            'items' => $this->gameLikeService->attachLikeInfo($items, $user),
            'total' => $total
        ];
    }

    /**
     * Получить игры, которые лайкнул пользователь
     * 
     * @return array{items: array, total: int}
     */
    public function getUserLikedGames(User $user, int $page, int $limit): array
    {
        $likes = $this->gameLikeService->getUserLikedGames($user, $page, $limit);
        
        return $likes;
    }

    /**
     * Обновить игру
     */
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

    /**
     * Удалить игру
     */
    public function deleteGame(int $id, User $user): void
    {
        $game = $this->findGameOrFail($id);
        $this->checkAccess($game, $user);

        $this->entityManager->remove($game);
        $this->entityManager->flush();
    }

    /**
     * Проверить доступ пользователя к игре
     */
    public function checkAccess(Game $game, User $user): void
    {
        if (!$user->isAdmin() && !$user->isGameAuthor($game)) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }
    }

    /**
     * Найти игру или выбросить исключение
     */
    private function findGameOrFail(int $id): Game
    {
        $game = $this->gameRepository->find($id);
        if (!$game) {
            throw new ApiException(ErrorCode::GAME_NOT_FOUND);
        }
        return $game;
    }
}
