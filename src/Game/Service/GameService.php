<?php

namespace App\Game\Service;

use App\Game\DTO\Request\GameListFilters;
use App\Game\DTO\Request\UpdateGameRequest;
use App\Game\Entity\Game;
use App\Game\Repository\GameLikeRepository;
use App\Game\Repository\GameRepository;
use App\Security\Voter\GameVoter;
use App\Shared\Enum\GameActivityLevel;
use App\Shared\Enum\ErrorCode;
use App\Shared\Enum\GameLocationType;
use App\Shared\Exception\ApiException;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GameService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GameRepository $gameRepository,
        private readonly GameLikeRepository $gameLikeRepository,
        private readonly AuthorizationCheckerInterface $authChecker,
    ) {}

    public function getPublicGames(GameListFilters $filters, ?User $user = null): array
    {
        $result = $this->gameRepository->findPublicGamesWithFilters($filters);

        return [
            'items' => $this->attachLikeInfo($result['items'], $user),
            'total' => $result['total']
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

    public function getTopLikedGames(int $page, int $limit, ?User $user = null): array
    {
        $result = $this->gameRepository->findTopLikedGames($page, $limit);

        return [
            'items' => $this->attachLikeInfo($result['items'], $user),
            'total' => $result['total'],
        ];
    }

    public function getGame(int $id): Game
    {
        $game = $this->findGameOrFail($id);

        if (!$game->isPublic()) {
            $this->checkAccess($game);
        }

        return $game;
    }

    public function updateGame(int $id, UpdateGameRequest $request): Game
    {
        $game = $this->findGameOrFail($id);
        $this->checkAccess($game);

        if ($request->title !== null) {
            $game->setTitle($request->title);
        }
        if ($request->description !== null) {
            $game->setDescription($request->description);
        }
        if ($request->age !== null) {
            $game->setAge($request->age);
        }
        if ($request->players !== null) {
            $game->setPlayers($request->players);
        }
        if ($request->duration !== null) {
            $game->setDuration($request->duration);
        }
        if ($request->locationType !== null) {
            $game->setLocationType(GameLocationType::from($request->locationType));
        }
        if ($request->fieldWidth !== null) {
            $game->setFieldWidth($request->fieldWidth);
        }
        if ($request->fieldLength !== null) {
            $game->setFieldLength($request->fieldLength);
        }
        if ($request->activityLevel !== null) {
            $game->setActivityLevel(GameActivityLevel::from($request->activityLevel));
        }
        if ($request->requisites !== null) {
            $game->setRequisites($request->requisites);
        }
        if ($request->isPublic !== null) {
            $game->setIsPublic($request->isPublic);
        }
        if ($request->locationDescription !== null) {
            $game->setLocationDescription($request->locationDescription);
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($game);

        return $game;
    }

    public function deleteGame(int $id): void
    {
        $game = $this->findGameOrFail($id);
        $this->checkAccess($game);

        $this->entityManager->remove($game);
        $this->entityManager->flush();
    }

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

    private function findGameOrFail(int $id): Game
    {
        $game = $this->gameRepository->find($id);
        if (!$game) {
            throw new ApiException(ErrorCode::GAME_NOT_FOUND);
        }
        return $game;
    }

    private function checkAccess(Game $game): void
    {
        if (!$this->authChecker->isGranted(GameVoter::MANAGE, $game)) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }
    }
}