<?php

namespace App\Service;

use App\DTO\Requests\CreateStageRequest;
use App\DTO\Requests\UpdateStageRequest;
use App\Entity\Game;
use App\Entity\Stage;
use App\Entity\User;
use App\Enum\ErrorCode;
use App\Exception\ApiException;
use Doctrine\ORM\EntityManagerInterface;

class StageService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GameService $gameService,
    ) {}

    public function create(CreateStageRequest $request, User $user): Stage
    {
        $game = $this->gameService->getGame($request->gameId, $user);
        $this->checkAccess($game, $user);

        $stage = new Stage();
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

    public function update(?Stage $stage, UpdateStageRequest $request, User $user): Stage
    {
        if (!$stage) {
            throw new ApiException(ErrorCode::STAGE_NOT_FOUND);
        }
        $this->checkAccess($stage->getGame(), $user);

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

    public function delete(?Stage $stage, User $user): void
    {
        if (!$stage) {
            throw new ApiException(ErrorCode::STAGE_NOT_FOUND);
        }

        $this->checkAccess($stage->getGame(), $user);

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