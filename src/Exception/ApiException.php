<?php

namespace App\Exception;

use App\Enum\ErrorCode;

class ApiException extends \RuntimeException
{
    public function __construct(
        private readonly ErrorCode $errorCode,
        ?string $customMessage = null
    ) {
        parent::__construct($customMessage ?? $errorCode->getMessage());
    }

    public function getErrorCode(): ErrorCode
    {
        return $this->errorCode;
    }
}