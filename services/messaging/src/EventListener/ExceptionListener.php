<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function __construct(
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Only handle API requests
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $statusCode = 500;
        $message = 'Internal server error';

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        }

        // Log the actual error for debugging
        $this->logger?->error('API Exception', [
            'path' => $request->getPathInfo(),
            'message' => $exception->getMessage(),
            'class' => get_class($exception),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Include debug info temporarily
        $debugMessage = $message;
        if ($statusCode === 500) {
            $debugMessage = $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine();
        }

        $response = new JsonResponse([
            'error' => true,
            'message' => $debugMessage,
            'code' => $statusCode,
        ], $statusCode);

        $event->setResponse($response);
    }
}

