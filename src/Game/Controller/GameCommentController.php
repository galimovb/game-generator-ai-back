<?php

namespace App\Game\Controller;

use App\Game\DTO\Request\CreateCommentRequest;
use App\Game\DTO\Request\UpdateCommentRequest;
use App\Game\DTO\Response\GameCommentResponse;
use App\Game\Service\GameCommentService;
use App\Shared\DTO\Response\ApiResponse;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/games/{gameId}/comments', name: 'app_comments_')]
class GameCommentController extends AbstractController
{
    public function __construct(
        private readonly GameCommentService $commentService,
        private readonly ApiResponse $apiResponse,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(
        int $gameId,
        Request $request,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);

        $result = $this->commentService->getGameComments($gameId, $page, $limit, $user);

        return $this->apiResponse->success([
            'items' => array_map(
                fn ($comment) => GameCommentResponse::fromEntity($comment),
                $result['items']
            ),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total'],
            ],
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        int $gameId,
        #[MapRequestPayload] CreateCommentRequest $request,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $comment = $this->commentService->createComment($gameId, $request, $user);

        return $this->apiResponse->success(
            GameCommentResponse::fromEntity($comment),
            201
        );
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(
        int $id,
        #[MapRequestPayload] UpdateCommentRequest $request,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $comment = $this->commentService->updateComment($id, $request, $user);

        return $this->apiResponse->success(
            GameCommentResponse::fromEntity($comment)
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $this->commentService->deleteComment($id, $user);

        return $this->apiResponse->success(null, 204);
    }
}
