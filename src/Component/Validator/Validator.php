<?php

namespace App\Component\Validator;

use App\Component\Validator\Exceptions\InvalidTypeException;
use App\Component\Validator\Exceptions\NotArrayException;
use App\Component\Validator\Exceptions\NotBooleanException;
use App\Component\Validator\Exceptions\NotObjectException;
use App\Component\Validator\Exceptions\NotStringException;

/**
 * Компонент валидации
 *
 * Class Validator
 * @package App\Component\ResponseBuilder
 */
final class Validator
{
    /**
     * Минимальный допустимый код HTTP Error.
     *
     * @var int
     */
    public const ERROR_HTTP_CODE_MIN = 400;

    /**
     * Максимально допустимый код HTTP Error.
     *
     * @var int
     */
    public const ERROR_HTTP_CODE_MAX = 599;

    /**
     * Минимальный допустимый код HTTP Access.
     *
     * @var int
     */
    public const OK_HTTP_CODE_MIN = 200;

    /**
     * Максимально допустимый код HTTP Access.
     *
     * @var int
     */
    public const OK_HTTP_CODE_MAX = 299;

    /**
     * Гарантирует, что $val имеет тип boolean.
     *
     * @param string $varName Ключ, используемый в случае исключения.
     * @param mixed $value Переменная для проверки.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsBool(string $varName, $value): void
    {
        self::assertIsType($varName, $value, [Type::BOOLEAN], NotBooleanException::class);
    }

    /**
     * Гарантирует, что $val имеет тип integer.
     *
     * @param string $varName Ключ, используемый в случае исключения.
     * @param mixed $value Переменная для проверки.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsInt(string $varName, $value): void
    {
        self::assertIsType($varName, $value, [Type::INTEGER], NotBooleanException::class);
    }

    /**
     * Гарантирует, что $val имеет тип array.
     *
     * @param string $varName Ключ, используемый в случае исключения.
     * @param mixed $value Переменная для проверки.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsArray(string $varName, $value): void
    {
        self::assertIsType($varName, $value, [Type::ARRAY], NotArrayException::class);
    }

    /**
     * Гарантирует, что данный $val имеет тип object.
     *
     * @param string $varName Ключ, используемый в случае исключения.
     * @param mixed $value Переменная для проверки.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsObject(string $varName, $value): void
    {
        self::assertIsType($varName, $value, [Type::OBJECT], NotObjectException::class);
    }

    /**
     * Гарантирует, что $val имеет тип string.
     *
     * @param string $varName Ключ, используемый в случае исключения.
     * @param mixed $value Переменная для проверки.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsString(string $varName, $value): void
    {
        self::assertIsType($varName, $value, [Type::STRING], NotStringException::class);
    }

    /**
     * Гарантирует вхождение $val в диапазон $min-$max.
     *
     * @param string $varName Ключ, используемый в случае исключения.
     * @param mixed $value Переменная для проверки.
     * @param int $min Минимальное допустимое значение (включительно).
     * @param int $max Максимальное допустимое значение (включительно).
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public static function assertIsIntRange(string $varName, $value, int $min, int $max): void
    {
        self::assertIsInt($varName, $value);

        if ($min > $max) {
            throw new \InvalidArgumentException(
                \sprintf('%s: Недопустимый диапазон для "%s".', __FUNCTION__, $varName)
            );
        }

        if (($min > $value) || ($value > $max)) {
            throw new \OutOfBoundsException(
                \sprintf(
                    'Значение "%s" (%d) вне границ. Должно быть между %d-%d включительно.',
                    $varName,
                    $value,
                    $min,
                    $max
                )
            );
        }
    }

    /**
     * Гарантирует, что тип элемента $item, включен в список допустимых типов $allowed_types.
     *
     * @param string $varName Ключ, используемый в случае исключения.
     * @param mixed $value Переменная для проверки.
     * @param array $allowedTypes Массив допустимых типов для $value, т.е. [Type::INTEGER].
     * @param string $exClass Класс исключения.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsType(
        string $varName,
        $value,
        array $allowedTypes,
        string $exClass = InvalidTypeException::class
    ): void {
        $type = \gettype($value);
        if (!\in_array($type, $allowedTypes, true)) {
            throw new $exClass($varName, $type, $allowedTypes);
        }
    }

    /**
     * Гарантирует, что данный код $http_code является допустимым кодом для ответа на ошибку.
     *
     * @param int $httpCode
     */
    public static function assertErrorHttpCode(int $httpCode): void
    {
        self::assertIsInt('http_code', $httpCode);
        self::assertIsIntRange(
            'http_code',
            $httpCode,
            self::ERROR_HTTP_CODE_MIN,
            self::ERROR_HTTP_CODE_MAX
        );
    }

    /**
     * Гарантирует, что указанный код $http_code действителен для ответа, указывающего на успешную операцию.
     *
     * @param int $httpCode
     */
    public static function assertOkHttpCode(int $httpCode): void
    {
        self::assertIsInt('http_code', $httpCode);
        self::assertIsIntRange('http_code', $httpCode, self::OK_HTTP_CODE_MIN, self::OK_HTTP_CODE_MAX);
    }

    /**
     * Гарантирует, что $obj является экземпляром указанного класса $cls.
     *
     * @param string $varName Ключ, используемый в случае исключения.
     * @param object $obj Объект проверки.
     * @param string $cls Класс для проверки.
     *
     * @throws \InvalidArgumentException
     */
    public static function assertInstanceOf(string $varName, object $obj, string $cls): void
    {
        if (!($obj instanceof $cls)) {
            throw new \InvalidArgumentException(
                \sprintf('Ожидается, что "%s" должен быть экземпляром "%s".', $varName, $cls)
            );
        }
    }

    /**
     * Гарантирует, что в массиве присутствует ключ
     *
     * @param string $varName Ключ, используемый в случае исключения.
     * @param array $array Массив в котором происходит поиск.
     * @param string $key Искомый ключ.
     *
     * @throws \InvalidArgumentException
     */
    public static function assertArrayKeyContains(string $varName, array $array, string $key): void
    {
        if (!\in_array($key, $array)) {
            throw new \InvalidArgumentException(
                \sprintf('В массиве "%s" должен присутствовать элемент с ключом "%s".', $varName, $key)
            );
        }
    }

    /**
     * Гарантирует, что $val положительный (true)
     *
     * @param string $varName
     * @param bool $value
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsTrue(string $varName, bool $value): void
    {
        if ($value != true) {
            throw new \InvalidArgumentException(
                \sprintf('Ожидается, что "%s" должен быть "%s".', $varName, 'true')
            );
        }
    }

    /**
     * Гарантирует, что $val отрицательный (false)
     *
     * @param string $varName
     * @param bool $value
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsFalse(string $varName, bool $value): void
    {
        if ($value !== false) {
            throw new \InvalidArgumentException(
                \sprintf('Ожидается, что "%s" должен быть "%s".', $varName, 'false')
            );
        }
    }
}
