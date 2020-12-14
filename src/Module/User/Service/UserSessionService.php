<?php

namespace App\Module\User\Service;

use App\Entity\User;
use App\Entity\UserSession;
use App\Repository\UserSessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Сервис пользовательских сессий.
 *
 * @package App\Module\Auth\Service
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class UserSessionService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserSessionRepository
     */
    private $userSessionRepository;

    /**
     * @var ContainerBagInterface
     */
    private $params;

    /**
     * UserSessionService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserSessionRepository  $userSessionRepository
     * @param ContainerBagInterface  $params
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserSessionRepository $userSessionRepository,
        ContainerBagInterface $params
    ) {
        $this->entityManager = $entityManager;
        $this->userSessionRepository = $userSessionRepository;
        $this->params = $params;
    }

    /**
     * Удаляет просроченные сессии пользователя.
     *
     * @param User $user
     */
    public function deleteExpireSession(User $user): void
    {
        // Получает просроченные сессии для пользователя
        $sessions = $this->userSessionRepository->getExpireSessions(new \DateTime, $user);
        // Удаляет просроченные сессии
        $this->deleteSessions($sessions);
    }

    /**
     * Удаляет сессии.
     *
     * @param array|UserSession $sessions
     */
    public function deleteSessions($sessions): void
    {
        if (!$sessions) {
            return;
        }

        if (!is_array($sessions)) {
            $sessions = [$sessions];
        }

        foreach ($sessions as $session) {
            $this->entityManager->remove($session);
        }
        $this->entityManager->flush();
    }

    /**
     * Создаёт сессию пользователя.
     *
     * @param User        $user
     * @param string      $clientIp
     * @param string|null $fingerprint
     *
     * @return UserSession
     * @throws \Exception
     */
    public function createUserSession(User $user, string $clientIp, string $fingerprint = null): UserSession
    {
        // TODO: перенести в гидратор?
        $userSession = new UserSession();
        $userSession->setUser($user);
        $userSession->setIp($clientIp);
        $userSession->setExp(new \DateTime('+' . $this->params->get('refresh_token_lifetime') . ' hour'));
        $userSession->setFingerprint($fingerprint);
        // Сохранение
        $this->entityManager->persist($userSession);
        $this->entityManager->flush();

        return $userSession;
    }

    /**
     * Создаёт куку с refresh_token для сессии пользователя.
     *
     * @param UserSession $userSession
     *
     * @return Cookie
     */
    public function createUserSessionCookie(UserSession $userSession): Cookie
    {
        return Cookie::create(
            'refresh_token',
            $userSession->getUuid(),
            $userSession->getExp(),
            '/backend/auth',
            null,
            false,
            true
        );
    }
}
