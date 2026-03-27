<?php

namespace App\Game\Service;

use App\Game\DTO\Request\CreateStageRequest;
use App\Game\DTO\Request\UpdateStageRequest;
use App\Game\Entity\Game;
use App\Game\Entity\GameStage;
use App\Shared\Enum\ErrorCode;
use App\Shared\Exception\ApiException;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class GameStageService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GameService $gameService,
    ) {}

    public function create(int $gameId, CreateStageRequest $request, User $user): GameStage
    {
        $game = $this->gameService->getGame($gameId);
        $this->checkAccess($game, $user);

        $stage = new GameStage();
        $stage->setGame($game);
        $stage->setStageOrder($this->getNextOrder($game));
        $stage->setTitle($request->title);
        $stage->setDescription($request->description);
        $stage->setDuration($request->duration);
        $stage->setTasks($request->tasks);
        $stage->setProps($request->props);

        $this->entityManager->persist($stage);
        $this->entityManager->flush();

        return $stage;
    }

    public function update(int $gameId, ?GameStage $stage, UpdateStageRequest $request, User $user): GameStage
    {
        if (!$stage) {
            throw new ApiException(ErrorCode::STAGE_NOT_FOUND);
        }

        $game = $this->gameService->getGame($gameId);
        $this->checkAccess($game, $user);

        if ($request->title !== null) {
            $stage->setTitle($request->title);
        }
        if ($request->description !== null) {
            $stage->setDescription($request->description);
        }
        if ($request->duration !== null) {
            $stage->setDuration($request->duration);
        }
        if ($request->tasks !== null) {
            $stage->setTasks($request->tasks);
        }
        if ($request->props !== null) {
            $stage->setProps($request->props);
        }

        $this->entityManager->flush();

        return $stage;
    }

    public function delete(int $gameId, ?GameStage $stage, User $user): void
    {
        if (!$stage) {
            throw new ApiException(ErrorCode::STAGE_NOT_FOUND);
        }

        $game = $this->gameService->getGame($gameId);
        $this->checkAccess($game, $user);

        $this->entityManager->remove($stage);
        $this->entityManager->flush();
    }

    private function checkAccess(Game $game, User $user): void
    {
        if ($game->getAuthor()->getId() !== $user->getId() && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }
    }

    private function getNextOrder(Game $game): int
    {
        $max = 0;
        foreach ($game->getStages() as $stage) {
            if ($stage->getStageOrder() > $max) {
                $max = $stage->getStageOrder();
            }
        }
        return $max + 1;
    }
}