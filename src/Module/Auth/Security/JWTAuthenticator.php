<?php

namespace App\Module\Auth\Security;

use App\Component\Response\ResponseData;
use App\Component\Token\JWT;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class JWTAuthenticator
 * @package App\Module\Auth\Security
 */
class JWTAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ContainerBagInterface
     */
    private $params;

    /**
     * JWTAuthenticator constructor.
     *
     * @param UserRepository $userRepository
     * @param ContainerBagInterface $params
     */
    public function __construct(
        UserRepository $userRepository,
        ContainerBagInterface $params
    ) {
        $this->userRepository = $userRepository;
        $this->params = $params;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        return $request->headers->has('Authorization')
            && preg_match('/Bearer\s(\S+)/', $request->headers->get('Authorization'));
    }

    /**
     * @param Request $request
     * @return array|mixed
     */
    public function getCredentials(Request $request)
    {
        // Получаем токен доступа
        $jwt = str_replace('Bearer ', '', $request->headers->get('Authorization'));
        // Декодируем и проверяем токен
        try {
            $payload = JWT::decode($jwt, $this->params->get('app_secret'), 'HS256');
        } catch (\Exception $exp) {
            throw new AuthenticationException('Ошибка декодирования JWT: ' . $exp->getMessage());
        }

        // Проверяем наличие идентификатора пользователя
        if (empty($payload['uuid'])) {
            throw new AuthenticationException('В токене не найден идентификатор пользователя');
        }

        return $payload;
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return \App\Entity\User|UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $this->userRepository->findOneBy(['uuid' => $credentials['uuid']]);
        if (!$user) {
            throw new AuthenticationException('Пользователь не найден');
        }

        return $user;
    }

    /**
     * Проверка токена доступа в getCredentials().
     *
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * Ошибка авторизации.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(
            ResponseData::successArray('Ошибка авторизации пользователя'),
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Успешная авторизация.
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return null;
    }

    /**
     * Вызывается, когда анонимный пользователь запрашивает доступ к закрытому ресурсу (требующему аутентификации).
     *
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return ResponseUtils::jsonError(
            'Доступ запрещен для неавторизованных пользователей',
            $authException->getMessage(),
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
