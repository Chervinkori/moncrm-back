<?php

namespace App\Module\User\Hydrator\Strategy;

use App\Entity\User;
use Laminas\Hydrator\Strategy\StrategyInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Стратегия кодирования пароля пользователя.
 *
 * @package App\Module\User\Hydrator\Strategy
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class PasswordEncodeStrategy implements StrategyInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    public $encoder;

    /**
     * @required
     *
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param mixed       $value
     * @param object|null $object
     *
     * @return mixed
     */
    public function extract($value, ?object $object = null)
    {
        return $value;
    }

    /**
     * @param mixed      $value
     * @param array|null $data
     *
     * @return mixed|string
     */
    public function hydrate($value, ?array $data): string
    {
        return $this->encoder->encodePassword(new User(), $value);
    }
}
