<?php

namespace App\Shared\Exception;

use RuntimeException;

/**
 * Base exception for all domain/business logic errors
 */
class DomainException extends RuntimeException
{
    public function __construct(
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

