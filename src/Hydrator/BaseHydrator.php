<?php

namespace App\Hydrator;

use Laminas\Hydrator\AbstractHydrator;

/**
 * Базовый класс гидратора.
 *
 * @package App\Hydrator
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
abstract class BaseHydrator
{
    /**
     * Создаёт экземпляр гидратора и устанавливает стратегии.
     *
     * @return AbstractHydrator Экземпляр гидратора.
     */
    public function create(): AbstractHydrator
    {
        $hydrator = $this->getHydrator();
        $this->addStrategies($hydrator);
        $this->setNamingStrategies($hydrator);

        return $hydrator;
    }

    /**
     * @return AbstractHydrator Экземпляр гидратора.
     */
    abstract protected function getHydrator(): AbstractHydrator;

    /**
     * Добавляет стратегии к гидратору.
     *
     * @param AbstractHydrator $hydrator Экземпляр гидратора.
     */
    abstract protected function addStrategies(AbstractHydrator &$hydrator): void;

    /**
     * Добавляет стратегии нейминга к гидратору.
     *
     * @param AbstractHydrator $hydrator Экземпляр гидратора.
     */
    abstract protected function setNamingStrategies(AbstractHydrator &$hydrator): void;
}
