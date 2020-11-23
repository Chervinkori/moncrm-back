<?php

namespace App\Component\Response;

use App\Component\Validator\Type;
use App\Component\Validator\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Абстрактный класс сборщика ответа.
 *
 * @package App\Component\Response
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
abstract class AResponseBuilder
{
    /** @var bool */
    protected $success = null;

    /** @var int|null */
    protected $httpCode = null;

    /** @var array */
    protected $httpHeaders = [];

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Дополнительные параметры сборщика.
     *
     * @var array
     */
    protected $params = [];

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Код HTTP по умолчанию для использования с успешными ответами
     *
     * @var int
     */
    public const DEFAULT_HTTP_CODE_OK = Response::HTTP_OK;

    /**
     * Код HTTP по умолчанию, который будет использоваться с ответами об ошибках
     *
     * @var int
     */
    public const DEFAULT_HTTP_CODE_ERROR = Response::HTTP_BAD_REQUEST;

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Сообщения по умолчанию
     */

    /** @var string */
    public const MSG_VALIDATION_ERROR = 'Ошибка валидации данных';

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Ключи по умолчанию
     */

    /** @var string */
    public const KEY_SUCCESS = 'success';
    /** @var string */
    public const KEY_MESSAGE = 'message';
    /** @var string */
    public const KEY_META = 'meta';
    /** @var string */
    public const KEY_DATA = 'data';
    /** @var string */
    public const KEY_DEBUG = 'debug';
    /** @var string */
    public const KEY_FIELD = 'field';
    /** @var string */
    public const KEY_VALUE = 'value';

    /** @var string */
    public const KEY_TYPE = 'type';
    /** @var string */
    public const KEY_TRACE = 'trace';
    /** @var string */
    public const KEY_FILE = 'file';
    /** @var string */
    public const KEY_LINE = 'line';

    /** @var string */
    public const KEY_BODY = 'body';
    /** @var string */
    public const KEY_COOKIES = 'cookies';
    /** @var string */
    public const KEY_HEADERS = 'headers';

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Приватный конструктор.
     * Используйте статические метод create() для получения экземпляра Builder.
     *
     * @param array $params Дополнительные параметры сборщика.
     */
    protected function __construct(array $params = [])
    {
        $this->params = $params;
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Устанавливаем статус выполнения.
     *
     * @param bool $success Статус выполнения ($success = true|false).
     *
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
     * @param array|null $httpHeaders
     *
     * @return $this
     */
    public function withHttpHeaders(array $httpHeaders = null): self
    {
        Validator::assertIsType('httpHeaders', $httpHeaders, [Type::ARRAY, Type::NULL]);

        $this->httpHeaders = $httpHeaders ?? [];

        return $this;
    }

    /**
     * Строит и возвращает готовый объект ответа. Поскольку при построении параметры не изменяются, безопасно
     * вызывать build() несколько раз, для получения нового объекта ответа. Также безопасно изменять любой набор
     * параметров заранее и после вызывать build(), чтобы получить новый объект ответа, включающий новые изменения.
     *
     * @return Response Готовый Http-ответ.
     */
    public function build(): Response
    {
        Validator::assertIsBool('success', $this->success);

        if ($this->success) {
            $httpCode = $this->httpCode ?? self::DEFAULT_HTTP_CODE_OK;
            Validator::assertOkHttpCode($httpCode);
        } else {
            $httpCode = $this->httpCode ?? self::DEFAULT_HTTP_CODE_ERROR;
            Validator::assertErrorHttpCode($httpCode);
        }

        // Формируем данные для ответа
        $data = $this->buildResponseData();
        // Валидируем данные ответа
        $this->validationResponseData($data);

        return new JsonResponse(
            $data,
            $httpCode,
            $this->httpHeaders
        );
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Создаёт экземпляр строителя ответа.
     *
     * @param array $params
     *
     * @return AResponseBuilder Экземпляр строителя ответов.
     */
    abstract public static function create(array $params = []);

    /**
     * Создаёт стандартизированный массив ответов API. Это окончательный метод, вызываемый во всем конвейере, прежде
     * чем мы вернём окончательный JSON обратно клиенту. Если вы хотите манипулировать своим ответом, это место для
     * этого. Если APP_DEBUG установлено значение true, поле code _ hex будет добавлено в отчет JSON для упрощения
     * отладки вручную.
     *
     * @return array Тело ответа в виде массива.
     */
    abstract protected function buildResponseData(): array;

    /**
     * Валидация данных ответа. В случае ошибки вернуть исключение.
     *
     * @param array $data Данные ответа для валидации.
     */
    abstract protected function validationResponseData(array $data);
}
