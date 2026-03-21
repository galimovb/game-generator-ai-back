<?php


namespace App\Service;

use App\Entity\GameLike;
use App\Entity\User;
use App\Enum\ErrorCode;
use App\Exception\ApiException;
use App\Repository\GameLikeRepository;
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