<?php

namespace App\Component\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Класс создания HTTP-ответа в формате JSON.
 *
 * @package App\Component\Response
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class JsonResponse
{
    /**
     * @var array
     */
    private $params;

    /**
     * JsonResponse constructor.
     *
     * @param KernelInterface $kernel Ядро с параметрами.
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->params = [
            'debug' => $kernel->isDebug()
        ];
    }

    /**
     * Создаёт билдер ответа.
     *
     * @param bool $success Статус выполнения ($success = true|false).
     *
     * @return JsonResponseBuilder Экземпляр строителя ответов.
     */
    public function createResponseBuilder(bool $success = true): JsonResponseBuilder
    {
        return JsonResponseBuilder::create($this->params)->setSuccess($success);
    }

    /**
     * Возвращает успешный объект ответа.
     *
     * @param object|array|null $data        Данные ответа.
     * @param string|null       $message     Дополнительное сообщение.
     * @param array|null        $meta        Мета-данные
     * @param array|object|null $debugData   Данные отладки. Передаются в ответ если kernel.isDebug = true.
     * @param integer|null      $httpCode    HTTP-код ответа. По умолчанию 200.
     * @param array|null        $httpHeaders HTTP заголовки для включения в объект ответа.
     *
     * @return Response Готовый успешный Http-ответ.
     */
    public function success(
        $data = null,
        string $message = null,
        array $meta = null,
        $debugData = null,
        int $httpCode = null,
        array $httpHeaders = null
    ): Response {
        return JsonResponseBuilder::create($this->params)
            ->setSuccess(true)
            ->withData($data)
            ->withMessage($message)
            ->withMeta($meta)
            ->withDebugData($debugData)
            ->withHttpCode($httpCode)
            ->withHttpHeaders($httpHeaders)
            ->build();
    }

    /**
     * Возвращает объект ответа с ошибкой.
     *
     * @param string|null       $message     Сообщение ошибки. Если $data является ConstraintViolationListInterface и
     *                                       $message является null - сообщение об ошибки валидации по умолчанию
     *                                       JsonResponseBuilder::MSG_VALIDATION_ERROR.
     * @param mixed|null        $data        Данные ответа.
     * @param array|object|null $debugData   Данные отладки. Передаются в ответ если kernel.isDebug = true.
     * @param int|null          $httpCode    HTTP-код ответа. По умолчанию 400.
     * @param array|null        $httpHeaders HTTP заголовки для включения в объект ответа.
     *
     * @return Response Готовый ошибочный Http-ответ.
     */
    public function error(
        string $message = null,
        $data = null,
        $debugData = null,
        int $httpCode = null,
        array $httpHeaders = null
    ): Response {
        return JsonResponseBuilder::create($this->params)
            ->setSuccess(false)
            ->withMessage($message)
            ->withData($data)
            ->withDebugData($debugData)
            ->withHttpCode($httpCode)
            ->withHttpHeaders($httpHeaders)
            ->build();
    }
}
