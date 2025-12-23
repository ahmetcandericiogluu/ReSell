<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        
        // Log the exception for debugging
        error_log('[ExceptionListener] Exception: ' . get_class($exception));
        error_log('[ExceptionListener] Message: ' . $exception->getMessage());
        error_log('[ExceptionListener] File: ' . $exception->getFile() . ':' . $exception->getLine());
        
        $response = new JsonResponse();

        if ($exception instanceof NotFoundHttpException) {
            $response->setData([
                'error' => 'Not Found',
                'message' => $exception->getMessage(),
            ]);
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        } elseif ($exception instanceof AccessDeniedHttpException) {
            $response->setData([
                'error' => 'Forbidden',
                'message' => $exception->getMessage(),
            ]);
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
        } elseif ($exception instanceof ValidationFailedException) {
            $violations = [];
            foreach ($exception->getViolations() as $violation) {
                $violations[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            
            $response->setData([
                'error' => 'Validation Failed',
                'errors' => $violations,
            ]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } elseif ($exception instanceof HttpExceptionInterface) {
            $response->setData([
                'error' => 'Error',
                'message' => $exception->getMessage(),
            ]);
            $response->setStatusCode($exception->getStatusCode());
        } else {
            // For non-HTTP exceptions, return 500
            $response->setData([
                'error' => 'Internal Server Error',
                'message' => $exception->getMessage(),
                'type' => get_class($exception),
                'file' => $exception->getFile() . ':' . $exception->getLine(),
            ]);
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }
}

