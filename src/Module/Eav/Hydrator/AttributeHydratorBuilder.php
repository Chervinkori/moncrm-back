<?php

namespace App\Module\Eav\Hydrator;

use App\Component\Hydrator\BaseHydratorBuilder;
use App\Entity\Eav\Type;
use Laminas\Hydrator\AbstractHydrator;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\Strategy\HydratorStrategy;

/**
 * Гидратор атрибутов EAV.
 *
 * @package App\Module\Eav\Hydrator
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class AttributeHydratorBuilder extends BaseHydratorBuilder
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
        $this->addStrategy('type', new HydratorStrategy(new ClassMethodsHydrator(), Type::class));
    }

    public function setNamingStrategies(): void
    {
    }
}
