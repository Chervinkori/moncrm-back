<?php

namespace App\Entity\Eav;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="eav_value_text", indexes={@ORM\Index(name="IDX_eav_value_text_value", columns={"value"})})
 *
 * @package App\Entity\EAV
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class ValueText extends Value
{


    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
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
     * @return ValueText
     */
    public function setValue(?string $value): ValueText
    {
        $this->value = $value;

        return $this;
    }
}
