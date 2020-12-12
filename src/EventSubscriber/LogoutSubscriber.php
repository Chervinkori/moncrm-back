<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Подписчик на событие выхода из системы
 *
 * @package App\EventListener
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class LogoutSubscriber implements EventSubscriberInterface
{
    /**
     * Дополнительная логика при выходе из системы
     *
     * @param LogoutEvent $event События выхода.
     */
    public function onLogout(LogoutEvent $event)
    {
        $response = $event->getResponse()->setStatusCode(Response::HTTP_NO_CONTENT);
        // Удаляет токен обновления доступа из cookie
        $response->headers->clearCookie('refresh_token', '/backend/auth');

        $event->setResponse($response);
    }

    /**
     * @return \string[][][]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => [
                ['onLogout']
            ],
        ];
    }
}
