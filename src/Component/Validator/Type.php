<?php

namespace App\Component\Validator;

/**
 * Класс контейнер для типов данных.
 *
 * @package App\Component\Validator
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
final class Type
{
    /** @var string */
    public const STRING = 'string';

    /** @var string */
    public const INTEGER = 'integer';

    /** @var string */
    public const BOOLEAN = 'boolean';

    /** @var string */
    public const ARRAY = 'array';

    /** @var string */
    public const OBJECT = 'object';

    /** @var string */
    public const DOUBLE = 'double';

    /** @var string */
    public const NULL = 'NULL';
}
