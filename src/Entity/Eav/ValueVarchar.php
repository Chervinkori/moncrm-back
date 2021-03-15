<?php

namespace App\Entity\Eav;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="eav_value_varchar", indexes={@ORM\Index(name="IDX_eav_value_varchar_value", columns={"value"})})
 *
 * @package App\Entity\EAV
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class ValueVarchar extends Value
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $value;

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     *
     * @return ValueVarchar
     */
    public function setValue(?string $value): ValueVarchar
    {
        $this->value = $value;

        return $this;
    }
}
