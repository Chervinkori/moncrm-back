<?php

namespace App\Component\Validator\Exceptions;

/**
 * Class InvalidTypeException
 *
 * @package App\Component\Validator\Exceptions
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class InvalidTypeException extends \Exception implements InvalidTypeExceptionContract
{
    /**
     * NotAnTypeBaseException constructor.
     *
     * @param string       $varName      Ключ, используемый в случае исключения.
     * @param string|array $allowedTypes Массив допустимых типов [Type::*].
     * @param string       $type         Текущий тип значения $value.
     */
    public function __construct(string $varName, string $type, array $allowedTypes)
    {
        switch (\count($allowedTypes)) {
            case 0:
                throw new \InvalidArgumentException('allowed_types массив не должен быть пустым.');
            case 1:
                $msg = 'Ожидается, что "%s" должен быть %s, но получен %s.';
                break;
            default;
                $msg = 'Ожидается, что "%s" должен быть одним из допустимых типов: %s, но получен %s.';
                break;
        }

        parent::__construct(\sprintf($msg, $varName, implode(', ', $allowedTypes), $type));
    }

}
