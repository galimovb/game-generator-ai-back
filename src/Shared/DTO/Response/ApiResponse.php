<?php

namespace App\Shared\DTO\Response;

use App\Shared\Enum\ErrorCode;
use App\Shared\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, int $statusCode = 200): JsonResponse
    {
        return new JsonResponse(
            [
                'result' => $data,
                'timestamp' => time()
            ],
            $statusCode
        );
    }

    public static function error(\Throwable $e): JsonResponse
    {
        if ($e instanceof ApiException) {
            return new JsonResponse(
                [
                    'error' => $e->getErrorCode()->value,
                    'errorMessage' => $e->getMessage()
                ],
                $e->getErrorCode()->getHttpCode()
            );
        }

        return new JsonResponse(
            [
                'error' => ErrorCode::INTERNAL_ERROR->value,
                'errorMessage' => $e->getMessage()
            ],
            ErrorCode::INTERNAL_ERROR->getHttpCode()
        );
    }
}