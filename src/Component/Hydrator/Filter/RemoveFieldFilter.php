<?php

namespace App\Component\Hydrator\Filter;

use Laminas\Hydrator\Filter\FilterInterface;

/**
 * Class RemoveFieldFilter
 *
 * @package App\Component\Hydrator\Filter
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class RemoveFieldFilter implements FilterInterface
{
    private $fields;

    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * Should return true, if the given filter does not match
     *
     * @param string $property The name of the property
     *
     * @return bool
     */
    public function filter(string $property): bool
    {
        $methodArr = \explode('::', $property);
        if (!empty($methodArr[1])) {
            $attribute = $property = $methodArr[1];
            if (strpos($property, 'get') === 0
                || strpos($property, 'set') === 0
                || strpos($property, 'has') === 0) {
                $attribute = substr($property, 3);
            }
            if (strpos($property, 'is') === 0) {
                $attribute = substr($property, 2);
            }

            $attribute = lcfirst($attribute);
        }
        if (!isset($attribute)) {
            return true;
        }

        // TODO

        if (in_array($attribute, $this->fields)) {
            return false;
        }

        return true;
    }
}
