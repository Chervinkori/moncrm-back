<?php

namespace App\Hydrator;

use App\Hydrator\Strategy\FirstUppercaseStrategy;
use App\Hydrator\Strategy\PasswordEncodeStrategy;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Строитель гидратора пользователя.
 *
 * @package App\Hydrator
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class UserHydratorBuilder extends AbstractHydratorBuilder
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * UserBuilder constructor.
     *
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * Добавляет стратегии к гидратору.
     */
    protected function addStrategies(): void
    {
        $hydrator = $this->getHydrator();
        $hydrator->addStrategy('password', new PasswordEncodeStrategy($this->encoder));
        $hydrator->addStrategy('firstname', new FirstUppercaseStrategy);
        $hydrator->addStrategy('middlename', new FirstUppercaseStrategy);
        $hydrator->addStrategy('lastname', new FirstUppercaseStrategy);
    }

    /**
     * Добавляет стратегии нейминга к гидратору.
     */
    protected function setNamingStrategies(): void
    {
    }
}
