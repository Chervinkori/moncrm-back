<?php

namespace App\Hydrator;

use App\Hydrator\Strategy\FirstUppercaseStrategy;
use App\Hydrator\Strategy\PasswordEncodeStrategy;
use Laminas\Hydrator\AbstractHydrator;
use Laminas\Hydrator\ClassMethodsHydrator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Гидратор пользователя.
 *
 * @package App\Hydrator
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class UserHydrator extends BaseHydrator
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @return AbstractHydrator
     */
    protected function getHydrator(): AbstractHydrator
    {
        return new ClassMethodsHydrator();
    }

    /**
     * @param AbstractHydrator $hydrator
     */
    protected function addStrategies(AbstractHydrator &$hydrator): void
    {
        $hydrator->addStrategy('password', new PasswordEncodeStrategy($this->encoder));
        $hydrator->addStrategy('firstname', new FirstUppercaseStrategy);
        $hydrator->addStrategy('middlename', new FirstUppercaseStrategy);
        $hydrator->addStrategy('lastname', new FirstUppercaseStrategy);
    }

    /**
     * @param AbstractHydrator $hydrator
     */
    protected function setNamingStrategies(AbstractHydrator &$hydrator): void
    {
    }
}
