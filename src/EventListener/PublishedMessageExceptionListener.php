<?php

namespace App\EventListener;

mb_internal_encoding("UTF-8");
use Psr\Log\LoggerInterface;
use App\Exception\UserInputException;
use App\Exception\PublishedMessageException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
class PublishedMessageExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof PublishedMessageException) {
            return;
        }
        $code = $exception instanceof UserInputException ? 400 : 500;
        $responseData = [
            'error' => [
                'code' => $code,
                'message' => $exception->getMessage()
            ]
        ];
        $event->setResponse(new JsonResponse($exception->getMessage(), $code));
    }
}