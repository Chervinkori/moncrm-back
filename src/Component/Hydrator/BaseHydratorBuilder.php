<?php

namespace App\Component\Hydrator;

use Laminas\Hydrator\AbstractHydrator;
use Laminas\Hydrator\NamingStrategy\NamingStrategyInterface;
use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Базовый класс строителя гидратора.
 *
 * @package App\Component\Hydrator
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
abstract class BaseHydratorBuilder
{
    /**
     * @var array Массив стратегии
     */
    protected $strategies = [];

    /**
     * @var array Массив стратегии наименований.
     */
    protected $namingStrategies = [];

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Добавляет в строитель стратегию.
     *
     * @param string            $name     Название
     * @param StrategyInterface $strategy Стратегия
     *
     * @return BaseHydratorBuilder
     */
    protected function addStrategy(string $name, StrategyInterface $strategy): self
    {
        $this->strategies[$name] = $strategy;

        return $this;
    }

    /**
     * Добавляет в строитель стратегию наименования.
     *
     * @param NamingStrategyInterface $strategy
     *
     * @return BaseHydratorBuilder
     */
    protected function setNamingStrategy(NamingStrategyInterface $strategy): self
    {
        $this->namingStrategies[] = $strategy;

        return $this;
    }

    /**
     * Создаёт экземпляр гидратора и устанавливает стратегии.
     *
     * @return AbstractHydrator Экземпляр гидратора.
     */
    public function build(): AbstractHydrator
    {
        // Добавляет в строитель стратегии
        $this->addStrategies();
        // Добавляет в строитель стратегии наименований
        $this->setNamingStrategies();

        // Получает объект гидратора
        $hydrator = $this->getObjectHydrator();

        // Устанавливает стратегии
        foreach ($this->strategies as $name => $strategy) {
            $hydrator->addStrategy($name, $strategy);
        }

        // Устанавливает стратегии наименований
        foreach ($this->namingStrategies as $namingStrategy) {
            $hydrator->setNamingStrategy($namingStrategy);
        }

        return $hydrator;
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Получить объект гидратора.
     *
     * @return AbstractHydrator Объект гидратора.
     */
    abstract protected function getObjectHydrator(): AbstractHydrator;

    /**
     * Добавляет в строитель стратегии.
     */
    abstract public function addStrategies(): void;

    /**
     * Добавляет в строитель стратегии наименований.
     *
     */
    abstract public function setNamingStrategies(): void;

}
