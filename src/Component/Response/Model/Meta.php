<?php

namespace App\Component\Response\Model;

use Laminas\Hydrator\ReflectionHydrator;

/**
 * Class Meta
 * @package App\Component\Response\Model
 */
class Meta
{
    /**
     * @var integer|null
     */
    private $items;

    /**
     * @var integer|null
     */
    private $pages;

    /**
     * @var integer|null
     */
    private $perPage;

    /**
     * @return int
     */
    public function getItems(): int
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getPages(): int
    {
        return $this->pages;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @param array $params
     */
    public function hydrate(array $params)
    {
        $hydrator = new ReflectionHydrator();
        $hydrator->hydrate($params, $this);
    }
}
