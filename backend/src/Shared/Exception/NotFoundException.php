<?php

namespace App\Shared\Exception;

/**
 * Thrown when a requested resource is not found
 */
class NotFoundException extends DomainException
{
    public function __construct(string $message = "Resource not found")
    {
        parent::__construct($message, 404);
    }
}

