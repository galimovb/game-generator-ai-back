<?php

namespace App\Game\Controller;

use App\Game\Service\GameLikeService;
use App\Shared\DTO\Response\ApiResponse;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/games')]
class GameLikeController extends AbstractController
{
    public function __construct(
        private readonly GameLikeService $gameLikeService,
        private readonly ApiResponse $apiResponse,
    ) {
    }

    #[Route('/{id}/like', name: 'game_like', methods: ['POST'])]
    public function like(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $this->gameLikeService->like($id, $user);

        return $this->apiResponse->success(null, 201);
    }

    #[Route('/{id}/like', name: 'game_unlike', methods: ['DELETE'])]
    public function unlike(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $this->gameLikeService->unlike($id, $user);

        return $this->apiResponse->success(null, 204);
    }
}
