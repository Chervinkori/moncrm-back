<?php

namespace App\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Стратегия, приводящая первый символ к верхнему регистру.
 *
 * @package App\Hydrator\Strategy
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class FirstUppercaseStrategy implements StrategyInterface
{
    /**
     * @param mixed       $value
     * @param object|null $object
     *
     * @return mixed|void
     */
    public function extract($value, ?object $object = null)
    {
    }

    /**
     * @param mixed      $value
     * @param array|null $data
     *
     * @return mixed|string
     */
    public function hydrate($value, ?array $data)
    {
        return ucfirst($value);
    }
}
