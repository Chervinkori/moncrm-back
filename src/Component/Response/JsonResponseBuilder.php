<?php

namespace App\Component\Response;

use App\Component\Validator\Type;
use App\Component\Validator\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Сборщик HTTP-ответа в формате JSON.
 *
 * @package App\Component\Response
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class JsonResponseBuilder extends AResponseBuilder
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
        Validator::assertIsType('meta', $meta, [Type::ARRAY, Type::NULL]);

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
        Validator::assertIsType(
            'data',
            $data,
            [
                Type::ARRAY,
                Type::BOOLEAN,
                Type::INTEGER,
                Type::STRING,
                Type::OBJECT,
                Type::NULL,
            ]
        );

        // Ограничения для объекта
        if (gettype($data) === Type::OBJECT) {
            // Разрешен только объект с ошибками валидации
            Validator::assertInstanceOf('data', $data, ConstraintViolationListInterface::class);
            // Только для отрицательного ответа
            Validator::assertIsFalse('success', $this->success);
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
        Validator::assertIsType('debugData', $debugData, [Type::ARRAY, Type::OBJECT, Type::NULL]);

        if (!is_array($debugData)) {
            $debugData = [$debugData];
        }

        foreach ($debugData as $data) {
            // Ограничения для объекта
            if (gettype($data) === Type::OBJECT) {
                // Разрешен только объект исключений или объект запроса
                Validator::assertInstanceOf('debugData', $data, [\Exception::class, Request::class]);
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
        Validator::assertIsType('message', $message, [Type::STRING, Type::NULL]);

        $this->message = $message;

        return $this;
    }

    /**
     * @return string|null Готовое сообщение, для вложения в тело ответа.
     */
    private function getMessage()
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
    private function getData()
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
    private function getDebugData()
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
                    self::KEY_BODY => $data->request->all(),
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
        Validator::assertKeyContains('params', $params, 'debug');
        Validator::assertIsBool('params.debug', $params['debug']);

        return new static($params);
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
        Validator::assertIsBool('success', $this->success);

        if ($data[self::KEY_SUCCESS]) {
            // TODO
            Validator::assertAtLeastOneIsNotNull([$data[self::KEY_DATA], $data[self::KEY_MESSAGE]]);
        } else {
            Validator::assertIsNotNull('message', $data[self::KEY_MESSAGE]);
        }
    }
}
