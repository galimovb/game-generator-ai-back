<?php

namespace App\Support\Controller;

use App\Shared\DTO\Response\ApiResponse;
use App\Support\DTO\Request\ChangeTicketPriorityRequest;
use App\Support\DTO\Request\ChangeTicketStatusRequest;
use App\Support\DTO\Response\TicketResponse;
use App\Support\Service\TicketService;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/tickets', name: 'app_tickets_')]
class TicketController extends AbstractController
{
    public function __construct(
        private readonly TicketService $ticketService
    ) {}

    #[Route('/{id}/take', name: 'take', methods: ['POST'])]
    public function take(
        int $id,
        #[CurrentUser] User $user
    ): JsonResponse {
        $ticket = $this->ticketService->takeTicket($id, $user);

        return ApiResponse::success(
            TicketResponse::fromEntity($ticket)
        );
    }

    #[Route('/{id}/status', name: 'status', methods: ['POST'])]
    public function changeStatus(
        int $id,
        #[MapRequestPayload] ChangeTicketStatusRequest $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        $ticket = $this->ticketService->changeStatus($id, $request, $user);

        return ApiResponse::success(
            TicketResponse::fromEntity($ticket)
        );
    }

    #[Route('/{id}/priority', name: 'priority', methods: ['POST'])]
    public function changePriority(
        int $id,
        #[MapRequestPayload] ChangeTicketPriorityRequest $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        $ticket = $this->ticketService->changePriority($id, $request, $user);

        return ApiResponse::success(
            TicketResponse::fromEntity($ticket)
        );
    }

    #[Route('/{id}/close', name: 'close', methods: ['POST'])]
    public function close(
        int $id,
        #[CurrentUser] User $user
    ): JsonResponse {
        $ticket = $this->ticketService->closeTicket($id, $user);

        return ApiResponse::success(
            TicketResponse::fromEntity($ticket)
        );
    }
}