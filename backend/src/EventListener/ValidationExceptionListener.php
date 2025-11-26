<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 0)]
class ValidationExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Handle validation errors from MapRequestPayload
        if ($exception instanceof UnprocessableEntityHttpException) {
            $previous = $exception->getPrevious();
            
            if ($previous instanceof ValidationFailedException) {
                $violations = $previous->getViolations();
                $errors = [];

                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }

                $response = new JsonResponse(
                    [
                        'error' => 'Doğrulama başarısız',
                        'violations' => $errors
                    ],
                    Response::HTTP_BAD_REQUEST
                );

                $event->setResponse($response);
                return;
            }
        }

        // Handle other HTTP exceptions with JSON response
        if ($exception instanceof HttpException) {
            $response = new JsonResponse(
                [
                    'error' => $exception->getMessage() ?: 'Bir hata oluştu'
                ],
                $exception->getStatusCode()
            );

            $event->setResponse($response);
        }
    }
}

