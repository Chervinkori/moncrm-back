<?php

namespace App\Component\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class AResponseBuilder
 * @package App\Component\Response
 */
abstract class AResponseBuilder
{
    /**
     * Дополнительные параметры сборщика.
     *
     * @var array
     */
    protected $params = [];

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

    /**
     * Сообщения по умолчанию
     */

    /** @var string */
    public const MSG_VALIDATION_ERROR = 'Ошибка валидации данных';

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
    public const KEY_VALIDATION_ERROR = 'data';
    /** @var string */
    public const KEY_DEBUG = 'debug';
    /** @var string */
    public const KEY_FIELD = 'field';
    /** @var string */
    public const KEY_VALUE = 'value';

    /** @var string */
    public const KEY_TRACE = 'trace';
    /** @var string */
    public const KEY_CLASS = 'class';
    /** @var string */
    public const KEY_FILE = 'file';
    /** @var string */
    public const KEY_LINE = 'line';


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

    /**
     * @param array $params
     * @return mixed
     */
    abstract public static function create(array $params = []);

    /**
     * Строит и возвращает готовый объект ответа. Поскольку при построении параметры не изменяются, безопасно
     * вызывать build() несколько раз, для получения нового объекта ответа. Также безопасно изменять любой набор
     * параметров заранее и после вызывать build(), чтобы получить новый объект ответа, включающий новые изменения.
     *
     * @return Response
     */
    abstract public function build(): Response;
}
