<?php

namespace App\Component\Response\Model;

/**
 * Class Error
 * @package App\Component\Response\Model
 */
class Error
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string|null
     */
    private $field;

    /**
     * @var mixed|null
     */
    private $param;

    /**
     * Error constructor.
     * @param string $message
     * @param string|null $field
     * @param mixed|null $param
     */
    public function __construct(string $message, ?string $field = null, $param = null)
    {
        $this->message = $message;
        $this->field = $field;
        $this->param = $param;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string|null
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * @return mixed|null
     */
    public function getParam()
    {
        return $this->param;
    }
}
