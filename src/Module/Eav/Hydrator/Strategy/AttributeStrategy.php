<?php

namespace App\Module\Eav\Hydrator\Strategy;

use App\Entity\Eav\Attribute;
use App\Module\Eav\Hydrator\AttributeHydratorBuilder;
use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Стратегия, разбирающая атрибут EAV.
 *
 * @package App\Module\Eav\Hydrator\Strategy
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class AttributeStrategy implements StrategyInterface
{
    public function extract($value, ?object $object = null)
    {
        if ($value instanceof Attribute) {
            $attributeHydrator = (new AttributeHydratorBuilder())->build();

            return $attributeHydrator->extract($value);
        }

        return null;
    }

    public function hydrate($value, ?array $data)
    {
        // TODO
    }
}
