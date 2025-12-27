<?php

namespace App\Shared\EventListener;

use App\Shared\Exception\DomainException;
use App\Shared\Exception\ValidationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Converts exceptions to JSON responses
 */
#[AsEventListener(event: 'kernel.exception')]
class ExceptionListener
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $environment
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        
        $statusCode = 500;
        $data = [
            'error' => 'Internal server error',
        ];

        // Handle domain exceptions
        if ($exception instanceof ValidationException) {
            $statusCode = $exception->getCode();
            $data = [
                'error' => $exception->getMessage(),
                'violations' => $exception->getViolations(),
            ];
        } elseif ($exception instanceof DomainException) {
            $statusCode = $exception->getCode() ?: 400;
            $data = [
                'error' => $exception->getMessage(),
            ];
        } elseif ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $data = [
                'error' => $exception->getMessage(),
            ];
        } else {
            // Log unexpected exceptions
            $this->logger->error('Unhandled exception: ' . $exception->getMessage(), [
                'exception' => $exception,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);

            // In dev environment, include exception details
            if ($this->environment === 'dev') {
                $data = [
                    'error' => $exception->getMessage(),
                    'class' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];
            }
        }

        $response = new JsonResponse($data, $statusCode);
        $event->setResponse($response);
    }
}

