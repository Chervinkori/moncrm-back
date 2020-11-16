<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\ORM\PersistentCollection;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Пользователь системы
 *
 * Class User
 * @package App\Entity
 *
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 *
 * @UniqueEntity(fields="email", message="Пользователь с таким адресом электронной почты уже существует.", groups={"common"})
 */
class User extends AbstractEntity implements UserInterface
{
    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->sessions = new ArrayCollection();
    }

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(type="string", length=180, unique=true)
     *
     * @Assert\NotBlank(groups={"common"})
     * @Assert\Email(groups={"common"})
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(groups={"common"})
     * @Assert\Length(min=2, max=50, groups={"common"})
     */
    protected $firstname;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\Length(min=2, max=50, groups={"common"})
     */
    protected $middlename;

    /**
     * @var string
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(groups={"common"})
     * @Assert\Length(min=2, max=50, groups={"common"})
     */
    protected $lastname;

    /**
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(groups={"common"})
     * @Assert\NotCompromisedPassword(groups={"common"})
     */
    protected $password;

    /**
     * @var ArrayCollection|PersistentCollection
     * @ORM\OneToMany(targetEntity="UserSession", mappedBy="user")
     */
    protected $sessions;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string|null
     */
    public function getMiddlename(): ?string
    {
        return $this->middlename;
    }

    /**
     * @param string|null $middlename
     */
    public function setMiddlename(?string $middlename): void
    {
        $this->middlename = $middlename;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return ArrayCollection|PersistentCollection
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * @param $sessions
     * @return $this
     */
    public function setSessions($sessions): self
    {
        $this->sessions = $sessions;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
