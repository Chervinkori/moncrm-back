<?php

namespace App\Entity\Eav;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="eav_type")
 *
 * @package App\Entity\EAV
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class Type
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @var array|null
     *
     * @ORM\Column(type="json_array", nullable=true, options={"jsonb"=true})
     */
    protected $additional;

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return Type
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Type
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return Type
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getAdditional(): ?array
    {
        return $this->additional;
    }

    /**
     * @param array|null $additional
     *
     * @return Type
     */
    public function setAdditional(?array $additional): Type
    {
        $this->additional = $additional;

        return $this;
    }
}
