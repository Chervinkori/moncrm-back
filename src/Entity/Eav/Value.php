<?php

namespace App\Entity\Eav;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="eav_value", indexes={@ORM\Index(name="IDX_eav_value_entity_id", columns={"entity_id"})})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name = "discr", type = "string")
 * @ORM\DiscriminatorMap({
 *     "default" = "Value",
 *     "text" = "ValueText",
 *     "varchar" = "ValueVarchar",
 *     "boolean" = "ValueBoolean",
 * })
 *
 * @package App\Entity\EAV
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
abstract class Value
{
    //text
    //varchar
    //date
    //time
    //datetime
    //number
    //file
    //image
    //
    //
    //
    //select
    //checkbox
    //radio

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $entity_id;

    /**
     * @var Attribute
     *
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     */
    protected $attribute;

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entity_id;
    }

    /**
     * @param int $entity_id
     *
     * @return Value
     */
    public function setEntityId(int $entity_id): self
    {
        $this->entity_id = $entity_id;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }

    /**
     * @param Attribute $attribute
     *
     * @return Value
     */
    public function setAttribute(Attribute $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }
}
