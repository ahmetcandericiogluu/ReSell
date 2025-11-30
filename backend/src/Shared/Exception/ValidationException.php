<?php

namespace App\Shared\Exception;

/**
 * Thrown when validation fails
 */
class ValidationException extends DomainException
{
    private array $violations = [];

    public function __construct(string $message = "Validation failed", array $violations = [])
    {
        parent::__construct($message, 422);
        $this->violations = $violations;
    }

    public function getViolations(): array
    {
        return $this->violations;
    }
}

