<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSession[]    findAll()
 * @method UserSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSession::class);
    }

    /**
     * Получить просроченные сессии.
     * Если передали пользователя - просроченные сессии пользователя.
     *
     * @param \DateTime $exp
     * @param User|null $user
     * @return UserSession[]
     */
    public function getExpireSessions(\DateTime $exp, User $user = null): array
    {
        $builder = $this->createQueryBuilder('session')
            ->where('session.exp < :exp')
            ->setParameter('exp', $exp);

        if ($user) {
            $builder->andWhere('session.user = :user')->setParameter('user', $user);
        }

        return $builder->getQuery()->getResult();
    }

    /**
     * Получить активные сессии.
     * Если передали пользователя - активные сессии пользователя.
     * Доп. параметры для условия.
     *
     * @param User|null $user
     * @param string|null $ip
     * @param string|null $fingerprint
     * @return int|mixed|string
     */
    public function getActiveSessions(User $user = null, string $ip = null, string $fingerprint = null)
    {
        $builder = $this->createQueryBuilder('session')
            ->where('session.exp > :date')
            ->setParameter('date', new \DateTime);

        if ($user) {
            $builder->andWhere('session.user = :user')->setParameter('user', $user);
        }
        if ($ip) {
            $builder->andWhere('session.ip = :ip')->setParameter('ip', $ip);
        }
        if ($fingerprint) {
            $builder->andWhere('session.fingerprint = :fingerprint')->setParameter('fingerprint', $fingerprint);
        }

        return $builder->getQuery()->getResult();
    }
}
