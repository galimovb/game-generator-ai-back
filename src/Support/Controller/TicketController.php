<?php

namespace App\Support\Controller;

use App\Shared\DTO\Response\ApiResponse;
use App\Support\DTO\Request\ChangeTicketPriorityRequest;
use App\Support\DTO\Request\ChangeTicketStatusRequest;
use App\Support\DTO\Request\CreateTicketRequest;
use App\Support\DTO\Response\TicketResponse;
use App\Support\Service\TicketService;
use App\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/tickets', name: 'app_tickets_')]
class TicketController extends AbstractController
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly ApiResponse $apiResponse
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateTicketRequest $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        $ticket = $this->ticketService->createTicket($request, $user);

        return $this->apiResponse->success(
            TicketResponse::fromEntity($ticket),
            201
        );
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function getList(
        Request $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        $status = $request->query->get('status');
        $search = $request->query->get('search');

        $result = $this->ticketService->getTicketList($user, $page, $limit, $status, $search);

        return $this->apiResponse->success([
            'items' => array_map(
                fn($ticket) => TicketResponse::fromEntity($ticket),
                $result['items']
            ),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total']
            ]
        ]);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(
        int $id,
        #[CurrentUser] User $user
    ): JsonResponse {
        $ticket = $this->ticketService->getTicket($id, $user);

        return $this->apiResponse->success(
            TicketResponse::fromEntity($ticket)
        );
    }

    #[Route('/{id}/take', name: 'take', methods: ['POST'])]
    public function take(
        int $id,
        #[CurrentUser] User $user
    ): JsonResponse {
        $ticket = $this->ticketService->takeTicket($id, $user);

        return $this->apiResponse->success(
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

        return $this->apiResponse->success(
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

        return $this->apiResponse->success(
            TicketResponse::fromEntity($ticket)
        );
    }

    #[Route('/{id}/close', name: 'close', methods: ['POST'])]
    public function close(
        int $id,
        #[CurrentUser] User $user
    ): JsonResponse {
        $ticket = $this->ticketService->closeTicket($id, $user);

        return $this->apiResponse->success(
            TicketResponse::fromEntity($ticket)
        );
    }
}