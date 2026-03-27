<?php

namespace App\Support\Controller;

use App\Shared\DTO\Response\ApiResponse;
use App\Support\DTO\Request\CreateTicketMessageRequest;
use App\Support\DTO\Request\UpdateTicketMessageRequest;
use App\Support\DTO\Response\TicketMessageResponse;
use App\Support\Service\TicketMessageService;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/tickets/{ticketId}/messages', name: 'app_ticket_messages_')]
class TicketMessageController extends AbstractController
{
    public function __construct(
        private readonly TicketMessageService $messageService
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        int $ticketId,
        #[MapRequestPayload] CreateTicketMessageRequest $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        $message = $this->messageService->createMessage($ticketId, $request, $user);

        return ApiResponse::success(
            TicketMessageResponse::fromEntity($message),
            201
        );
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(
        int $id,
        #[MapRequestPayload] UpdateTicketMessageRequest $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        $message = $this->messageService->updateMessage($id, $request, $user);

        return ApiResponse::success(
            TicketMessageResponse::fromEntity($message)
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        #[CurrentUser] User $user
    ): JsonResponse {
        $this->messageService->deleteMessage($id, $user);

        return ApiResponse::success(null, 204);
    }
}