<?php

namespace App\EventSubscriber\Kernel;

use App\Utility\ResponseUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ExceptionSubscriber
 * @package App\EventSubscriber
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $isDebug;

    /**
     * ExceptionSubscriber constructor.
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->isDebug = $kernel->isDebug();
    }

    /**
     * @return array|\string[][][]
     */
    public static function getSubscribedEvents()
    {
        // TODO
        return [
//            KernelEvents::EXCEPTION => [
//                ['setResponse']
//            ],
        ];
    }

    /**
     * @param ExceptionEvent $event
     */
    public function setResponse(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // Debug mode
        if ($this->isDebug) {
            $response = ResponseUtils::jsonError(
                $exception->getMessage(),
                [
                    'exception' => [
                        'type' => get_class($exception),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'trace' => $exception->getTrace()
                    ]
                ],
                method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : Response::HTTP_BAD_REQUEST
            );
            $event->setResponse($response);

            return;
        }

        // TODO: дописать условия
        if ($exception instanceof NotFoundHttpException) {
            $response = ResponseUtils::jsonError(
                'Страница не найдена',
                $exception->getMessage(),
                $exception->getStatusCode(),
                $exception->getHeaders()
            );
        } elseif ($exception instanceof AccessDeniedHttpException) {
            $response = ResponseUtils::jsonError(
                'Недостаточно прав доступа',
                $exception->getMessage(),
                $exception->getStatusCode(),
                $exception->getHeaders()
            );
        } else {
            $response = ResponseUtils::jsonError(
                'Непредвиденная ошибка',
                $exception->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $event->setResponse($response);
    }
}
