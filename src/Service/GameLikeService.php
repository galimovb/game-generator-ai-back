<?php

namespace App\Service;

use App\Contracts\Service\GameLikeServiceInterface;
use App\Contracts\Service\GameManagementServiceInterface;
use App\Entity\Game;
use App\Entity\GameLike;
use App\Entity\User;
use App\Enum\ErrorCode;
use App\Exception\ApiException;
use App\Repository\GameLikeRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Сервис управления лайками игр
 */
class GameLikeService implements GameLikeServiceInterface
{
    public function __construct(
        private readonly GameLikeRepository $gameLikeRepository,
        private readonly GameRepository $gameRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Добавить лайк игре
     */
    public function addLike(int $gameId, User $user): GameLike
    {
        $game = $this->findGameOrFail($gameId);
        
        $existingLike = $this->gameLikeRepository->findOneBy([
            'author' => $user,
            'game' => $game,
        ]);

        if ($existingLike) {
            throw new ApiException(ErrorCode::LIKE_EXIST);
        }

        $like = new GameLike();
        $like->setGame($game);
        $like->setAuthor($user);

        $this->entityManager->persist($like);
        $this->entityManager->flush();

        return $like;
    }

    /**
     * Удалить лайк
     */
    public function removeLike(int $gameId, User $user): void
    {
        $game = $this->findGameOrFail($gameId);
        
        $like = $this->gameLikeRepository->findOneBy([
            'author' => $user,
            'game' => $game,
        ]);

        if (!$like) {
            throw new ApiException(ErrorCode::LIKE_NOT_FOUND);
        }

        $this->entityManager->remove($like);
        $this->entityManager->flush();
    }

    /**
     * Проверить, лайкнул ли пользователь игру
     */
    public function isLikedByUser(int $gameId, User $user): bool
    {
        return $this->gameLikeRepository->findOneBy([
            'author' => $user,
            'game' => $gameId,
        ]) !== null;
    }

    /**
     * Получить ID игр, которые лайкнул пользователь
     * 
     * @return int[]
     */
    public function getLikedGameIds(User $user, array $gameIds): array
    {
        return $this->gameLikeRepository->findLikedGameIds($user, $gameIds);
    }

    /**
     * Прикрепить информацию о лайках к списку игр
     * 
     * @param Game[] $games
     * @return array Массив с элементами ['game' => Game, 'isLiked' => bool]
     */
    public function attachLikeInfo(array $games, ?User $user): array
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

    /**
     * Получить игры, которые лайкнул пользователь, с пагинацией
     * 
     * @return array{items: array, total: int}
     */
    public function getUserLikedGames(User $user, int $page, int $limit): array
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
