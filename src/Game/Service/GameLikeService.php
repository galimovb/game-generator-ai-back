<?php


namespace App\Game\Service;

use App\Game\Entity\GameLike;
use App\Game\Repository\GameLikeRepository;
use App\Shared\Enum\ErrorCode;
use App\Shared\Exception\ApiException;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class GameLikeService
{
    public function __construct(
        private GameLikeRepository $repo,
        private EntityManagerInterface $em,
        private GameService $gameService,
    ) {}

    public function like(int $gameId, User $user): void
    {
        $game = $this->gameService->getGame($gameId);
        $favorite = $this->repo->findOneBy([
            'author' => $user,
            'game' => $game,
        ]);

        if($favorite){
            throw new ApiException(ErrorCode::LIKE_EXIST);
        }

        $favorite = new GameLike();
        $favorite->setGame($game);
        $favorite->setAuthor($user);

        $this->em->persist($favorite);
        $this->em->flush();
    }

    public function unlike(int $gameId, User $user): void
    {
        $game = $this->gameService->getGame($gameId);
        $favorite = $this->repo->findOneBy([
            'author' => $user,
            'game' => $game,
        ]);

        if (!$favorite) {
            throw new ApiException(ErrorCode::LIKE_NOT_FOUND);
        }

        $this->em->remove($favorite);
        $this->em->flush();
    }
}