<?php

namespace App\Entity;

use App\Repository\UserSessionRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Сущность пользовательских сессий.
 *
 * @ORM\Entity(repositoryClass=UserSessionRepository::class)
 * @ORM\Table(name="`user_session`", indexes={@ORM\Index(name="idx_user_session_exp", columns={"exp"})})
 *
 * @package App\Entity
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class UserSession extends AbstractEntity
{
    /**
     * @var string
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true, options={"comment":"Уникальный идентификатор"})
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     *
     * @Assert\Uuid(groups={"main"})
     */
    protected $uuid;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="sessions")
     * @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid")
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(type="string", options={"comment":"Ip-адрес"})
     *
     * @Assert\Ip(groups={"main"})
     */
    protected $ip;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", options={"comment":"Срок жизни сессии (до)"})
     */
    protected $exp;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true, options={"comment":"Цифровой отпечаток пользоватея"})
     */
    protected $fingerprint;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return \DateTime
     */
    public function getExp(): \DateTime
    {
        return $this->exp;
    }

    /**
     * @param \DateTime $exp
     */
    public function setExp(\DateTime $exp): void
    {
        $this->exp = $exp;
    }

    /**
     * @return string|null
     */
    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }

    /**
     * @param string|null $fingerprint
     */
    public function setFingerprint(?string $fingerprint): void
    {
        $this->fingerprint = $fingerprint;
    }
}
