<?php

namespace App\EventSubscriber;

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
 *
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
     *
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
        try {
            $exp = $event->getThrowable();

            // Debug mode
            if ($this->isDebug) {
                $e = $exp;
                do {
                    $exceptionData[] = [
                        'type' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ];
                } while ($e = $e->getPrevious());

                $response = ResponseUtils::jsonError(
                    $exp->getMessage(),
                    ['exceptions' => $exceptionData],
                    method_exists($exp, 'getStatusCode') ? $exp->getStatusCode() : Response::HTTP_BAD_REQUEST
                );

                $event->setResponse($response);
                return;
            }

            // TODO: дописать условия
            if ($exp instanceof NotFoundHttpException) {
                $response = ResponseUtils::jsonError(
                    'Страница не найдена',
                    $exp->getMessage(),
                    $exp->getStatusCode(),
                    $exp->getHeaders()
                );
            } elseif ($exp instanceof AccessDeniedHttpException) {
                $response = ResponseUtils::jsonError(
                    'Недостаточно прав доступа',
                    $exp->getMessage(),
                    $exp->getStatusCode(),
                    $exp->getHeaders()
                );
            } else {
                throw $exp;
            }
        } catch (\Throwable $exp) {
            $response = ResponseUtils::jsonError(
                'Непредвиденная ошибка',
                $exp->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $event->setResponse($response);
    }
}
