<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ExceptionListener
{
    public function __construct(
        private readonly ?LoggerInterface $logger = null,
        private readonly string $appEnv = 'prod',
        private readonly bool $showDetailedErrors = false
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Only handle API requests
        if (!str_starts_with($request->getPathInfo(), '/api') && $request->getPathInfo() !== '/health') {
            return;
        }

        // Log the error
        $this->logger?->error('API Exception', [
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'message' => $exception->getMessage(),
            'class' => get_class($exception),
            'file' => $exception->getFile() . ':' . $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $errorData = [
            'error' => true,
            'code' => $statusCode,
        ];

        if ($exception instanceof ValidationFailedException) {
            $violations = [];
            foreach ($exception->getViolations() as $violation) {
                $violations[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            $errorData['message'] = 'Validation failed';
            $errorData['errors'] = $violations;
            $errorData['code'] = Response::HTTP_BAD_REQUEST;
            $statusCode = Response::HTTP_BAD_REQUEST;
        } elseif ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $errorData['message'] = $exception->getMessage();
            $errorData['code'] = $statusCode;
        } else {
            // Internal server error
            $showDetails = $this->appEnv === 'dev' || $this->showDetailedErrors;
            
            if ($showDetails) {
                $errorData['message'] = $exception->getMessage();
                $errorData['exception'] = get_class($exception);
                $errorData['file'] = basename($exception->getFile()) . ':' . $exception->getLine();
                
                // In dev, show full path and trace
                if ($this->appEnv === 'dev') {
                    $errorData['file'] = $exception->getFile() . ':' . $exception->getLine();
                    $errorData['trace'] = array_slice(explode("\n", $exception->getTraceAsString()), 0, 10);
                }
            } else {
                $errorData['message'] = 'Internal server error';
            }
        }

        $response = new JsonResponse($errorData, $statusCode);
        $event->setResponse($response);
    }
}

