<?php

namespace App\Shared\Exception;

/**
 * Thrown when user is not authorized to perform an action
 */
class UnauthorizedException extends DomainException
{
    public function __construct(string $message = "Unauthorized")
    {
        parent::__construct($message, 403);
    }
}

