<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $throwable = $event->getThrowable();
        $previous = $throwable->getPrevious();

        if ($previous instanceof ValidationFailedException
            || $throwable instanceof ValidationFailedException
        ) {
            $violations = ($previous instanceof ValidationFailedException ? $previous : $throwable)->getViolations();
            $details = [];

            foreach ($violations as $violation) {
                $details[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => (string) $violation->getMessage(),
                ];
            }

            $event->setResponse(new JsonResponse([
                'error' => [
                    'code' => 'validation_failed',
                    'message' => 'The given data was invalid.',
                    'details' => $details,
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY));

            return;
        }

        if ($throwable instanceof HttpExceptionInterface) {
            $status = $throwable->getStatusCode();
            $code = match ($status) {
                Response::HTTP_BAD_REQUEST => 'bad_request',
                Response::HTTP_CONFLICT => 'conflict',
                Response::HTTP_UNAUTHORIZED => 'unauthorized',
                Response::HTTP_FORBIDDEN => 'forbidden',
                Response::HTTP_NOT_FOUND => 'not_found',
                Response::HTTP_TOO_MANY_REQUESTS => 'too_many_requests',
                default => 'http_error',
            };

            $event->setResponse(new JsonResponse([
                'error' => [
                    'code' => $code,
                    'message' => $throwable->getMessage() !== ''
                        ? $throwable->getMessage()
                        : Response::$statusTexts[$status] ?? 'Error',
                ],
            ], $status));
        }
    }
}
