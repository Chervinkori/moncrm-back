<?php

namespace App\Module\Eav\Hydrator;

use App\Component\Hydrator\BaseHydratorBuilder;
use Laminas\Hydrator\AbstractHydrator;
use Laminas\Hydrator\ClassMethodsHydrator;

/**
 * Гидратор типов EAV.
 *
 * @package App\Module\Eav\Hydrator
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class TypeHydratorBuilder extends BaseHydratorBuilder
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
    }

    public function setNamingStrategies(): void
    {
    }
}
