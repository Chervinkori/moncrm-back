<?php

namespace App\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Class FirstUppercaseStrategy
 *
 * @package App\Hydrator\Strategy
 */
class FirstUppercaseStrategy implements StrategyInterface
{
    public function extract($value, ?object $object = null)
    {
    }

    public function hydrate($value, ?array $data)
    {
        return ucfirst($value);
    }
}
