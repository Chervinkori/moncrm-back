<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Подписчик json-запроса.
 *
 * @package App\EventSubscriber
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class JsonRequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['contentJsonDecode']
            ],
        ];
    }

    /**
     * Декодирование данных контента.
     *
     * @param RequestEvent $event
     */
    public function contentJsonDecode(RequestEvent $event)
    {
        try {
            $request = $event->getRequest();
            if ($request->getContentType() === 'json') {
                if (!empty($request->getContent())) {
                    $contentData = json_decode($request->getContent(), true);
                    if (!empty($contentData) && is_array($contentData)) {
                        $request->request->add($contentData);
                    }
                }
            }
        } catch (\Exception $exp) {
            // TODO: залогировать
        }
    }
}
