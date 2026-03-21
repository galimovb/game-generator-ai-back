<?php

namespace App\Controller;

use App\DTO\Responses\ApiResponse;
use App\Entity\User;
use App\Service\GameLikeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/games')]
class GameLikeController extends AbstractController
{
    public function __construct(
        private readonly GameLikeService $gameLikeService
    ) {}

    #[Route('/{id}/like', name: 'game_like', methods: ['POST'])]
    public function like(int $id,  #[CurrentUser] User $user): JsonResponse
    {
        $this->gameLikeService->like($id, $user);

        return ApiResponse::success(null, 201);
    }

    #[Route('/{id}/like', name: 'game_unlike', methods: ['DELETE'])]
    public function unlike(int $id,  #[CurrentUser] User $user): JsonResponse
    {
        $this->gameLikeService->unlike($id, $user);

        return ApiResponse::success(null, 204);
    }
}