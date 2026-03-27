<?php

namespace App\Game\Service;

use App\Game\DTO\Request\CreateCommentRequest;
use App\Game\DTO\Request\UpdateCommentRequest;
use App\Game\Entity\GameComment;
use App\Game\Repository\GameCommentRepository;
use App\Shared\Enum\ErrorCode;
use App\Shared\Exception\ApiException;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class GameCommentService
{
    public function __construct(
        private readonly GameService $gameService,
        private readonly GameCommentRepository $commentRepository,
        private readonly EntityManagerInterface $em
    ) {}

    /**
     * @throws \Exception
     */
    public function getGameComments(int $gameId, int $page, int $limit, User $user): array
    {
        $game = $this->gameService->getGame($gameId, $user);

        $items = $this->commentRepository->findBy(
            ['game' => $game],
            ['createdAt' => 'ASC'],
            $limit,
            ($page - 1) * $limit
        );

        $total = $this->commentRepository->count(['game' => $game]);

        return [
            'items' => $items,
            'total' => $total
        ];
    }

    /**
     * @throws \Exception
     */
    public function createComment(int $gameId, CreateCommentRequest $request, User $user): GameComment
    {
        $game = $this->gameService->getGame($gameId, $user);

        $comment = new GameComment();
        $comment->setGame($game);
        $comment->setAuthor($user);
        $comment->setText($request->text);

        if ($request->parentId) {
            $parent = $this->findCommentOrFail($request->parentId);

            if ($parent->getGame()->getId() !== $gameId) {
                throw new ApiException(
                    ErrorCode::FORBIDDEN,
                );
            }

            $comment->setParent($parent);
        }

        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }

    public function updateComment(int $id, UpdateCommentRequest $request, User $user): GameComment
    {
        $comment = $this->findCommentOrFail($id);

        $this->checkCommentOwnership($comment, $user);

        $comment->setText($request->text);
        $comment->setUpdatedAt(new \DateTimeImmutable());

        $this->em->flush();

        return $comment;
    }

    public function deleteComment(int $id, User $user): void
    {
        $comment = $this->findCommentOrFail($id);

        $this->checkCommentOwnership($comment, $user);

        $this->em->remove($comment);
        $this->em->flush();
    }

    private function findCommentOrFail(int $commentId): GameComment
    {
        $comment = $this->commentRepository->find($commentId);

        if (!$comment) {
            throw new ApiException(ErrorCode::COMMENT_NOT_FOUND);
        }

        return $comment;
    }

    private function checkCommentOwnership(GameComment $comment, User $user): void
    {
        if ($comment->getAuthor()->getId() !== $user->getId()) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }
    }
}