<?php

namespace App\Listener;

use App\Shared\DTO\Response\ApiResponse;
use App\Shared\Enum\ErrorCode;
use App\Shared\Exception\ApiException;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JwtExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ApiResponse $apiResponse
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            JWTExpiredEvent::class => 'onExpired',
            JWTInvalidEvent::class => 'onInvalid',
            JWTNotFoundEvent::class => 'onNotFound',
        ];
    }

    public function onExpired(JWTExpiredEvent $event): void
    {
        $event->setResponse(
            $this->apiResponse->error(new ApiException(ErrorCode::TOKEN_EXPIRED))
        );
    }

    public function onInvalid(JWTInvalidEvent $event): void
    {
        $event->setResponse(
            $this->apiResponse->error(new ApiException(ErrorCode::TOKEN_INVALID))
        );
    }

    public function onNotFound(JWTNotFoundEvent $event): void
    {
        $event->setResponse(
            $this->apiResponse->error(new ApiException(ErrorCode::TOKEN_MISSING))
        );
    }
}