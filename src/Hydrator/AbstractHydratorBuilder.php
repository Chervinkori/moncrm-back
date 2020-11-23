<?php

namespace App\Hydrator;

use Laminas\Hydrator\AbstractHydrator;
use Laminas\Hydrator\ClassMethodsHydrator;

/**
 * Абстрактные класс строителя гидратора.
 *
 * @package App\Hydrator
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
abstract class AbstractHydratorBuilder
{
    /**
     * @var AbstractHydrator
     */
    private $hydrator;

    /**
     * Создаёт готовый к использованию экземпляр гидратора.
     *
     * @param string $hydratorClass Класс гидратора (по умолчанию ClassMethodsHydrator).
     *
     * @return AbstractHydrator
     */
    public function build(string $hydratorClass = ClassMethodsHydrator::class): AbstractHydrator
    {
        $this->hydrator = new $hydratorClass;
        $this->addStrategies();
        $this->setNamingStrategies();

        return $this->hydrator;
    }

    /**
     * Получает экземпляр гидратора.
     *
     * @return AbstractHydrator
     */
    protected function getHydrator(): AbstractHydrator
    {
        return $this->hydrator;
    }

    /**
     * Добавляет стратегии к гидратору.
     */
    abstract protected function addStrategies(): void;

    /**
     * Добавляет стратегии нейминга к гидратору.
     */
    abstract protected function setNamingStrategies(): void;
}
