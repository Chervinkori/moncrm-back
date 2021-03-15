<?php

namespace App\Module\Eav\Hydrator;

use App\Component\Hydrator\BaseHydratorBuilder;
use App\Module\Eav\Hydrator\Strategy\AttributeStrategy;
use Laminas\Hydrator\AbstractHydrator;
use Laminas\Hydrator\ClassMethodsHydrator;

/**
 * Гидратор значений EAV.
 *
 * @package App\Module\Eav\Hydrator
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class ValueHydratorBuilder extends BaseHydratorBuilder
{
    /**
     * @return AbstractHydrator
     */
    protected function getObjectHydrator(): AbstractHydrator
    {
        return new ClassMethodsHydrator();
    }

    public function addStrategies(): void
    {
        $this->addStrategy('attribute', new AttributeStrategy);
    }

    public function setNamingStrategies(): void
    {
    }
}
