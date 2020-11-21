<?php

namespace App\Hydrator;

use Laminas\Hydrator\AbstractHydrator;
use Laminas\Hydrator\ClassMethodsHydrator;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;

/**
 * Абстрактные строитель гидратора
 *
 * Class AbstractBuilder
 *
 * @package App\Hydrator
 */
abstract class AbstractHydratorBuilder
{
    /**
     * @var AbstractHydrator
     */
    private $hydrator;

    /**
     * Получить гидратор.
     *
     * @param string $hydratorClass Класс гидратора (по умолчанию ClassMethodsHydrator)
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
     * @return AbstractHydrator
     */
    protected function getHydrator(): AbstractHydrator
    {
        return $this->hydrator;
    }

    /**
     * Добавляет стратегии к гидратору
     */
    abstract protected function addStrategies(): void;

    /**
     * Добавляет стратегии нейминга к гидратору
     */
    abstract protected function setNamingStrategies(): void;
}
