<?php

namespace App\Component\Validator\Exceptions;

/**
 * Interface InvalidTypeExceptionContract
 *
 * @package App\Component\Validator\Exceptions
 */
interface InvalidTypeExceptionContract
{
    /**
     * InvalidTypeExceptionContract constructor.
     *
     * @param string $var_name
     * @param string $type
     * @param array  $allowed_types
     */
    public function __construct(string $var_name, string $type, array $allowed_types);
}
