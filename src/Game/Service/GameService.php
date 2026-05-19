<?php

namespace App\Game\Service;

use App\Game\DTO\Request\GameListFilters;
use App\Game\DTO\Request\UpdateGameRequest;
use App\Game\Entity\Game;
use App\Game\Repository\GameLikeRepository;
use App\Game\Repository\GameRepository;
use App\Security\Voter\GameVoter;
use App\Shared\Enum\ErrorCode;
use App\Shared\Enum\GameActivityLevel;
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
    ) {
    }

    public function getPublicGames(GameListFilters $filters, ?User $user = null): array
    {
        $result = $this->gameRepository->findPublicGamesWithFilters($filters);

        return [
            'items' => $this->attachLikeInfo($result['items'], $user),
            'total' => $result['total'],
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

        $games = array_map(fn ($like) => $like->getGame(), $likes);

        return [
            'items' => array_map(fn ($game) => [
                'game' => $game,
                'isLiked' => true,
            ], $games),
            'total' => $total,
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
            'total' => $total,
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

        if (null !== $request->title) {
            $game->setTitle($request->title);
        }
        if (null !== $request->description) {
            $game->setDescription($request->description);
        }
        if (null !== $request->age) {
            $game->setAge($request->age);
        }
        if (null !== $request->players) {
            $game->setPlayers($request->players);
        }
        if (null !== $request->duration) {
            $game->setDuration($request->duration);
        }
        if (null !== $request->locationType) {
            $game->setLocationType(GameLocationType::from($request->locationType));
        }
        if (null !== $request->fieldWidth) {
            $game->setFieldWidth($request->fieldWidth);
        }
        if (null !== $request->fieldLength) {
            $game->setFieldLength($request->fieldLength);
        }
        if (null !== $request->activityLevel) {
            $game->setActivityLevel(GameActivityLevel::from($request->activityLevel));
        }
        if (null !== $request->requisites) {
            $game->setRequisites($request->requisites);
        }
        if (null !== $request->isPublic) {
            $game->setIsPublic($request->isPublic);
        }
        if (null !== $request->locationDescription) {
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
            return array_map(fn ($game) => [
                'game' => $game,
                'isLiked' => false,
            ], $games);
        }

        $gameIds = array_map(fn ($game) => $game->getId(), $games);
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

    public function getAgeSafetyConstraints(int $age): string
    {
        return match (true) {
            $age <= 5 => "- Бег: не более 2 мин без остановки, дистанция до 20 м\n- Прыжки: только на месте или с места, высота не более 20 см\n- Предметы: не тяжелее 1 кг\n- Запрещено: столкновения, лазанье выше 1 м, спортивные снаряды (кольца, корзины), сложные правила",
            $age <= 8 => "- Бег: до 5 мин, дистанция до 70 м\n- Прыжки: в длину, невысокие препятствия до 40 см; спортивные кольца/корзины ЗАПРЕЩЕНЫ (высота 3+ м недостижима)\n- Предметы: не тяжелее 2 кг; камни и тяжёлые снаряды ЗАПРЕЩЕНЫ\n- Запрещено: прыжки с высоты > 50 см, силовые столкновения, поднятие других детей",
            $age <= 12 => "- Бег: до 10 мин, эстафеты допустимы\n- Прыжки: препятствия до 70 см\n- Предметы: не тяжелее 5 кг\n- Запрещено: поднятие партнёра, прыжки с высоты > 1 м, тяжёлые снаряды",
            $age <= 17 => "- Интенсивные нагрузки допустимы\n- Предметы: до 10 кг\n- Запрещено: жёсткий силовой контакт, высоты без страховки",
            default => '- Стандартные нагрузки, умеренный командный контакт допустим',
        };
    }
}
