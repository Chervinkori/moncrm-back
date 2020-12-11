<?php

namespace App\Component\Response\Builder;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Сборщик HTTP-ответа в формате JSON.
 *
 * @package App\Component\Response
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class JsonResponseBuilder extends BaseResponseBuilder
{
    /** @var array|null */
    protected $meta = null;

    /** @var mixed */
    protected $data = null;

    /** @var string */
    protected $message = null;

    /** @var array|null */
    protected $debugData = [];

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Добавляем в строитель ответов мета-информацию.
     *
     * @param array|null $meta Мета-данные.
     *
     * @return $this Экземпляр строителя.
     */
    public function withMeta($meta = null): self
    {
        $this->validate($meta, new Assert\Type('array'));
        $this->meta = $meta;

        return $this;
    }

    /**
     * Добавляем в строитель ответов данные.
     *
     * @param mixed|null $data Данные для ответа.
     *
     * @return $this Экземпляр строителя.
     */
    public function withData($data = null): self
    {
        $this->validate($data, new Assert\Type(['array', 'boolean', 'integer', 'string', 'object']));
        // Ограничения для объекта
        if (gettype($data) === 'object') {
            // Только для отрицательного ответа
            $this->validate($this->success, new Assert\IsFalse());
            // Разрешен только объект с ошибками валидации
            $this->validate($data, new Assert\Type(ConstraintViolationListInterface::class));
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Добавляем в строитель ответов отладочную информацию.
     * Отладочная информация попадёт в готовый ответ если в приложении включен режим отладки.
     *
     * @param array|object|null $debugData Отладочная информация.
     *
     * @return $this Экземпляр строителя.
     */
    public function withDebugData($debugData = null): self
    {
        $this->validate($debugData, new Assert\Type(['array', 'object']));

        if (!is_array($debugData)) {
            $debugData = [$debugData];
        }

        foreach ($debugData as $data) {
            // Ограничения для объекта
            if (gettype($data) === 'object') {
                // Разрешен только объект исключений или объект запроса
                $this->validate($data, new Assert\Type([\Exception::class, Request::class]));
            }
        }

        $this->debugData = $debugData;

        return $this;
    }

    /**
     * Добавляем в строитель ответов сообщение.
     *
     * @param string|null $message Сообщение.
     *
     * @return $this Экземпляр строителя.
     */
    public function withMessage(string $message = null): self
    {
        $this->validate($message, new Assert\Type('string'));

        $this->message = $message;

        return $this;
    }

    /**
     * @return string|null Готовое сообщение, для вложения в тело ответа.
     */
    private function getMessage(): ?string
    {
        $message = null;
        if ($this->data instanceof ConstraintViolationListInterface) {
            $message = self::MSG_VALIDATION_ERROR;
        } else {
            $message = $this->message;
        }

        return $message;
    }

    /**
     * @return array|null Готовый данные, для вложения в тело ответа.
     */
    private function getData(): ?array
    {
        $data = null;
        if ($this->data instanceof ConstraintViolationListInterface) {
            /** @var ConstraintViolation $error */
            foreach ($this->data as $error) {
                // Заполняем ошибками валидации
                $data[] = [
                    self::KEY_FIELD => $error->getPropertyPath(),
                    self::KEY_VALUE => $error->getInvalidValue(),
                    self::KEY_MESSAGE => $error->getMessage(),
                ];
            }
        } else {
            $data = $this->data;
        }

        return $data;
    }

    /**
     * @return array  Готовая отладочная информация, для вложения в тело ответа.
     */
    private function getDebugData(): array
    {
        $debugData = null;

        foreach ($this->debugData as $key => $data) {
            if ($data instanceof \Exception) {
                $exp = $data;
                do {
                    $debugData['exception'][] = [
                        self::KEY_TYPE => get_class($exp),
                        self::KEY_MESSAGE => $exp->getMessage(),
                        self::KEY_FILE => $exp->getFile(),
                        self::KEY_LINE => $exp->getLine(),
                        self::KEY_TRACE => $exp->getTraceAsString()
                    ];
                } while ($exp = $exp->getPrevious());
            } elseif ($data instanceof Request) {
                $debugData['request'] = [
                    self::KEY_METHOD => $data->getMethod(),
                    self::KEY_BODY => $data->isMethod('POST') ? $data->request->all() : $data->query->all(),
                    self::KEY_COOKIES => $data->cookies->all(),
                    self::KEY_HEADERS => $data->headers->all(),
                ];
            } else {
                if (!empty($data)) {
                    $debugData[$key] = $data;
                }
            }
        }

        return $debugData;
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Создаёт экземпляр билдера.
     *
     * @param array $params Дополнительные параметры сборщика.
     *
     * @return static Экземпляр строителя ответов.
     */
    public static function create(array $params = []): self
    {
        // Проверяем обязательный параметр 'debug'
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $params,
            new Assert\Collection(
                [
                    'fields' => [
                        'debug' => new Assert\Required(
                            [
                                new Assert\NotBlank(),
                                new Assert\Type('boolean')
                            ]
                        )
                    ]
                ]
            )
        );
        if ($violations->count() !== 0) {
            throw new ValidationFailedException($params, $violations);
        }

        return new static($params);
    }

    /**
     * Получить класс ответа.
     *
     * @return string Класс ответа.
     */
    protected function getResponseClass(): string
    {
        return JsonResponse::class;
    }

    /**
     * Создаёт стандартизированный массив ответов API. Это окончательный метод, вызываемый во всем конвейере, прежде
     * чем мы вернём окончательный JSON обратно клиенту. Если вы хотите манипулировать своим ответом, это место для
     * этого. Если APP_DEBUG установлено значение true, поле code _ hex будет добавлено в отчет JSON для упрощения
     * отладки вручную.
     *
     * @return array Тело ответа в виде массива.
     */
    protected function buildResponseData(): array
    {
        $response = [
            self::KEY_SUCCESS => $this->success,
            self::KEY_META => $this->meta,
            self::KEY_MESSAGE => $this->getMessage(),
            self::KEY_DATA => $this->getData()
        ];

        // Если включен режим отладки
        if ($this->params['debug']) {
            $response[self::KEY_DEBUG] = $this->getDebugData();
        }

        return $response;
    }

    /**
     * Валидация данных ответа. В случае ошибки вернуть исключение.
     *
     * @param array $data Данные ответа для валидации.
     */
    protected function validationResponseData(array $data)
    {
        $this->validate($this->success, new Assert\Type('boolean'));

        if ($data[self::KEY_SUCCESS]) {
            // Хотя бы один из параметров не пустой: self::KEY_DATA, self::KEY_MESSAGE
            $this->validate(
                $data,
                new Assert\AtLeastOneOf(
                    [
                        'constraints' => [
                            new Assert\Collection(['fields' => [self::KEY_DATA => new Assert\NotBlank()], 'allowExtraFields' => true]),
                            new Assert\Collection(['fields' => [self::KEY_MESSAGE => new Assert\NotBlank()], 'allowExtraFields' => true])
                        ]
                    ]
                )
            );
        } else {
            $this->validate($data[self::KEY_MESSAGE], new Assert\NotBlank());
        }
    }
}
