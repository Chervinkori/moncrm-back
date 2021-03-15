<?php

namespace App\Module\User\Hydrator;

use App\Component\Hydrator\BaseHydratorBuilder;
use App\Component\Hydrator\Strategy\FirstUppercaseStrategy;
use App\Module\User\Hydrator\Strategy\PasswordEncodeStrategy;
use Laminas\Hydrator\AbstractHydrator;
use Laminas\Hydrator\ClassMethodsHydrator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Гидратор пользователя.
 *
 * @package App\Module\User\Hydrator
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class UserHydratorBuilder extends BaseHydratorBuilder
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

    // -----------------------------------------------------------------------------------------------------------

    /**
     * @return AbstractHydrator
     */
    protected function getObjectHydrator(): AbstractHydrator
    {
        return new ClassMethodsHydrator();
    }

    /**
     * Добавляет в строитель стратегии.
     */
    public function addStrategies(): void
    {
        $this->addStrategy('password', new PasswordEncodeStrategy($this->encoder));
        $this->addStrategy('firstname', new FirstUppercaseStrategy);
        $this->addStrategy('middlename', new FirstUppercaseStrategy);
        $this->addStrategy('lastname', new FirstUppercaseStrategy);
    }

    /**
     * Добавляет в строитель стратегии наименований.
     */
    public function setNamingStrategies(): void
    {
        // $this->setNamingStrategy(NamingStrategyInterface $strategy);
    }
}
