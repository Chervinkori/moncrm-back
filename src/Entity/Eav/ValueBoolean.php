<?php

namespace App\Entity\Eav;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="eav_value_bool", indexes={@ORM\Index(name="IDX_eav_value_bool_value", columns={"value"})})
 *
 * @package App\Entity\EAV
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class ValueBoolean extends Value
{
    /**
     * @var boolean|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $value;

    public function getValue(): ?bool
    {
        return $this->value;
    }

    public function setValue(?bool $value): self
    {
        $this->value = $value;

        return $this;
    }
}
