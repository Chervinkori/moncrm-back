<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Абстрактная базовая сущность.
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 *
 * @package App\Entity
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
abstract class AbstractEntity
{
    /**
     * @var bool С функционалом предварительного обновления.
     */
    protected $withPreUpdate = true;

    /**
     * @var string
     * @ORM\Column(type="uuid", unique=true, options={"comment":"Уникальный идентификатор"})
     */
    protected $uuid;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", options={"comment":"Дата и время создания"})
     */
    protected $createdAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"Дата и время обновления"})
     */
    protected $updatedAt;

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     *
     * @return AbstractEntity
     */
    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return AbstractEntity
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return AbstractEntity
     */
    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @return $this
     */
    public function prePersist(): self
    {
        $this->createdAt = new \DateTime;

        return $this;
    }

    /**
     * @ORM\PreUpdate
     * @return $this
     */
    public function preUpdate(): self
    {
        if ($this->withPreUpdate) {
            $this->updatedAt = new \DateTime;
        }

        return $this;
    }
}
