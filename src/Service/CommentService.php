<?php

namespace App\Service;

use App\DTO\Requests\CreateCommentRequest;
use App\DTO\Requests\UpdateCommentRequest;
use App\Entity\Game;
use App\Entity\GameComment;
use App\Entity\User;
use App\Enum\ErrorCode;
use App\Exception\ApiException;
use App\Repository\GameCommentRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;

class CommentService
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