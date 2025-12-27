<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
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

        $response = new JsonResponse([
            'error' => true,
            'message' => $message,
            'code' => $statusCode,
        ], $statusCode);

        $event->setResponse($response);
    }
}

