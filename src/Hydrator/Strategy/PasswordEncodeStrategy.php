<?php

namespace App\Hydrator\Strategy;

use App\Entity\User;
use Laminas\Hydrator\Strategy\StrategyInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class PasswordEncodeStrategy
 *
 * @package App\Modules\Auth\Hydrator\Strategy
 */
class PasswordEncodeStrategy implements StrategyInterface
{
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

    public function extract($value, ?object $object = null)
    {
        return $value;
    }

    public function hydrate($value, ?array $data)
    {
        return $this->encoder->encodePassword(new User(), $value);
    }
}
