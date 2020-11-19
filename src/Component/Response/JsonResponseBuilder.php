<?php

namespace App\Component\Response;

use App\Component\Validator\Type;
use App\Component\Validator\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Сборщик ответа.
 *
 * Class JsonResponseBuilder
 * @package App\Component\Response
 */
class JsonResponseBuilder extends AResponseBuilder
{
    /** @var bool */
    protected $success = null;

    /** @var int|null */
    protected $httpCode = null;

    /** @var array|null */
    protected $meta = null;

    /** @var mixed */
    protected $data = null;

    /** @var ConstraintViolationListInterface|null */
    protected $validationErrors = null;

    /** @var string */
    protected $message = null;

    /** @var array */
    protected $httpHeaders = [];

    /** @var array */
    protected $debugData = [];

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Создаёт экземпляр билдера.
     *
     * @param array $params Дополнительные параметры сборщика.
     * @return static
     */
    public static function create(array $params = []): self
    {
        // Проверяем обязательный параметр 'debug'
        Validator::assertArrayKeyContains('params', $params, 'debug');
        Validator::assertIsBool('params.debug', $params['debug']);

        return new static($params);
    }

    /**
     * Устанавливаем статус выполнения.
     *
     * @param bool $success Статус выполнения ($success = true|false).
     * @return $this
     */
    public function setSuccess(bool $success = true): self
    {
        Validator::assertIsBool('success', $success);

        $this->success = $success;

        return $this;
    }

    /**
     * @param int|null $httpCode
     *
     * @return $this
     */
    public function withHttpCode(int $httpCode = null): self
    {
        Validator::assertIsType('httpCode', $httpCode, [Type::INTEGER, Type::NULL]);

        $this->httpCode = $httpCode;

        return $this;
    }

    /**
     * @param array|null $meta
     *
     * @return $this
     */
    public function withMeta($meta = null): self
    {
        Validator::assertIsType('meta', $meta, [Type::ARRAY, Type::NULL]);

        $this->meta = $meta;

        return $this;
    }

    /**
     * @param mixed|null $data
     *
     * @return $this
     */
    public function withData($data = null): self
    {
        Validator::assertIsType(
            'data',
            $data,
            [
                Type::ARRAY,
                Type::BOOLEAN,
                Type::INTEGER,
                Type::STRING,
                Type::NULL,
            ]
        );

        $this->data = $data;

        return $this;
    }

    /**
     * @param ConstraintViolationListInterface|null $validationErrors
     * @return $this
     */
    public function withValidationError(ConstraintViolationListInterface $validationErrors = null): self
    {
        Validator::assertIsType('validationErrors', $validationErrors, [Type::OBJECT, Type::NULL]);
        Validator::assertIsBool('success', $this->success);

        // Только для отрицательного ответа
        Validator::assertIsFalse('success', $this->success);

        $this->validationErrors = $validationErrors;

        return $this;
    }

    /**
     * @param array|null $debugData
     *
     * @return $this
     */
    public function withDebugData(array $debugData = null): self
    {
        Validator::assertIsType('debugData', $debugData, [Type::ARRAY, Type::NULL]);

        $this->debugData = $debugData;

        return $this;
    }

    /**
     * @param string|null $message
     *
     * @return $this
     */
    public function withMessage(string $message = null): self
    {
        Validator::assertIsType('message', $message, [Type::STRING, Type::NULL]);

        $this->message = $message;

        return $this;
    }

    /**
     * @param array|null $httpHeaders
     *
     * @return $this
     */
    public function withHttpHeaders(array $httpHeaders = null): self
    {
        Validator::assertIsType('http_headers', $httpHeaders, [Type::ARRAY, Type::NULL]);

        $this->httpHeaders = $httpHeaders ?? [];

        return $this;
    }

    /**
     * @return Response
     */
    public function build(): Response
    {
        if ($this->success) {
            $httpCode = $this->httpCode ?? self::DEFAULT_HTTP_CODE_OK;
            Validator::assertOkHttpCode($httpCode);
        } else {
            $httpCode = $this->http_code ?? self::DEFAULT_HTTP_CODE_ERROR;
            Validator::assertErrorHttpCode($httpCode);
        }

        return new JsonResponse(
            $this->buildResponseData(),
            $httpCode,
            $this->httpHeaders
        );
    }

    /**
     * Создаёт стандартизированный массив ответов API. Это окончательный метод, вызываемый во всем конвейере, прежде
     * чем мы вернём окончательный JSON обратно клиенту. Если вы хотите манипулировать своим ответом, это место для
     * этого. Если APP_DEBUG установлено значение true, поле code _ hex будет добавлено в отчет JSON для упрощения
     * отладки вручную.
     *
     * @return array Готовые данные для ответа.
     */
    protected function buildResponseData(): array
    {
        $response = [
            self::KEY_SUCCESS => $this->success,
            self::KEY_META => $this->meta,
            self::KEY_MESSAGE => $this->message,
            self::KEY_DATA => $this->data,
        ];

        // Ошибки валидации
        if ($this->validationErrors != null && $this->validationErrors->count() != 0) {
            // Если не установлено сообщение - выставляем по умолчанию
            $response[self::KEY_MESSAGE] = $response[self::KEY_MESSAGE] ?? self::MSG_VALIDATION_ERROR;

            // Разбираем ошибки валидации
            /** @var ConstraintViolation $error */
            foreach ($this->validationErrors as $error) {
                // Преобразуем в массив (предосторожность)
                $response[self::KEY_VALIDATION_ERROR] = (array)$response[self::KEY_VALIDATION_ERROR];
                // Заполняем ошибками валидации
                $response[self::KEY_VALIDATION_ERROR][] = [
                    self::KEY_FIELD => $error->getPropertyPath(),
                    self::KEY_VALUE => $error->getInvalidValue(),
                    self::KEY_MESSAGE => $error->getMessage(),
                ];
            }
        }

        if ($this->params['debug']) {
            $response[self::KEY_DEBUG] = $this->debugData;
        }

        return $response;
    }

}
