<?php

namespace App\Component\Response;

use App\Component\Response\Model\Error;
use App\Component\Response\Model\Meta;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\Hydrator\Strategy\CollectionStrategy;
use Laminas\Hydrator\Strategy\HydratorStrategy;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class ResponseData
 * @package App\Component\Response
 */
class ResponseData
{
    /**
     * @var Meta
     */
    private $meta;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var Error[]
     */
    private $error = [];

    /**
     * ResponseData constructor.
     */
    public function __construct()
    {
        $this->meta = new Meta();
    }

    /**
     * @return Meta
     */
    public function getMeta(): Meta
    {
        return $this->meta;
    }

    /**
     * @param int|null $items
     * @param int|null $pages
     * @param int|null $perPage
     * @return $this
     */
    public function setMeta(?int $items = null, ?int $pages = null, ?int $perPage = null): self
    {
        $this->meta->hydrate(['items' => $items, 'pages' => $pages, 'perPage' => $perPage]);

        return $this;
    }

    /**
     * @param array $metaParams
     * @return $this
     */
    public function setMetaParams(array $metaParams): self
    {
        $this->meta->hydrate($metaParams);

        return $this;
    }


    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return ResponseData
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return Error[]
     */
    public function getError(): array
    {
        return $this->error;
    }

    /**
     * @param string $message
     * @param string|null $field
     * @param mixed|null $param
     * @return $this
     */
    public function addError(string $message, ?string $field = null, $param = null): self
    {
        $this->error[] = new Error($message, $field, $param);

        return $this;
    }

    /**
     * @param ConstraintViolationListInterface $errors
     * @return $this
     */
    public function addValidationErrors(ConstraintViolationListInterface $errors): self
    {
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $this->error[] = new Error($error->getMessage(), $error->getPropertyPath(), $error->getInvalidValue());
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clearError(): self
    {
        $this->error = [];

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $hydrator = new ReflectionHydrator();
        $hydrator->addStrategy(
            'meta',
            new HydratorStrategy(new ReflectionHydrator(), Meta::class)
        );
        $hydrator->addStrategy(
            'error',
            new CollectionStrategy(new ReflectionHydrator(), Error::class)
        );
        $result = $hydrator->extract($this);

        // Чистим пустые элементы
        $result['meta'] = array_filter($result['meta']);

        return $result;
    }
}
