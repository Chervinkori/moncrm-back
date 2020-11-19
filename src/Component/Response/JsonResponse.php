<?php

namespace App\Component\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class JsonResponse
 * @package App\Component\Response
 */
class JsonResponse
{
    /**
     * @var array
     */
    private $params;

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
     * @return JsonResponseBuilder
     */
    public function createResponseBuilder(bool $success = true): JsonResponseBuilder
    {
        return JsonResponseBuilder::create($this->params)->setSuccess($success);
    }

    /**
     * Возвращает успешный объект ответа.
     *
     * @param object|array|null $data Данные ответа.
     * @param integer|null $http_code HTTP-код ответа.
     * @param array|null $http_headers HTTP заголовки для включения в объект ответа.
     *
     * @return Response
     */
    public function success(
        $data = null,
        int $http_code = null,
        array $http_headers = null
    ): Response {
        return JsonResponseBuilder::create($this->params)
            ->setSuccess(true)
            ->withData($data)
            ->withHttpCode($http_code)
            ->withHttpHeaders($http_headers)
            ->build();
    }

    /**
     * Возвращает объект ответа с ошибкой.
     *
     * @param object|array|null $data Данные ответа.
     * @param ConstraintViolationListInterface|null $validationErrors Ошибки валидации.
     * @param int|null $http_code HTTP-код ответа.
     * @param array|null $http_headers HTTP заголовки для включения в объект ответа.
     *
     * @return Response
     */
    public function error(
        $data = null,
        ConstraintViolationListInterface $validationErrors = null,
        int $http_code = null,
        array $http_headers = null
    ): Response {
        return JsonResponseBuilder::create($this->params)
            ->setSuccess(false)
            ->withData($data)
            ->withValidationError($validationErrors)
            ->withHttpCode($http_code)
            ->withHttpHeaders($http_headers)
            ->build();
    }
}
