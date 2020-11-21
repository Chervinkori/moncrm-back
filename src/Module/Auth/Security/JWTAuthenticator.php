<?php

namespace App\Module\Auth\Security;

use App\Component\Response\JsonResponse;
use App\Component\Token\JWT;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class JWTAuthenticator
 *
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
     * @var JsonResponse
     */
    private $jsonResponse;

    /**
     * JWTAuthenticator constructor.
     *
     * @param UserRepository        $userRepository
     * @param ContainerBagInterface $params
     * @param JsonResponse          $jsonResponse
     */
    public function __construct(
        UserRepository $userRepository,
        ContainerBagInterface $params,
        JsonResponse $jsonResponse

    ) {
        $this->userRepository = $userRepository;
        $this->params = $params;
        $this->jsonResponse = $jsonResponse;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        return $request->headers->has('Authorization')
            && preg_match('/Bearer\s(\S+)/', $request->headers->get('Authorization'));
    }

    /**
     * @param Request $request
     *
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
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return User|UserInterface|null
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
     * @param mixed         $credentials
     * @param UserInterface $user
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * Ошибка авторизации.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->jsonResponse->error(
            'Ошибка авторизации пользователя',
            null,
            [$request, $exception],
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Успешная авторизация.
     *
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey
     *
     * @return null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return null;
    }

    /**
     * Вызывается, когда анонимный пользователь запрашивает доступ к закрытому ресурсу (требующему аутентификации).
     *
     * @param Request                      $request
     * @param AuthenticationException|null $authException
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return $this->jsonResponse->error(
            'Доступ запрещён для неавторизованных пользователей',
            null,
            [$request, $authException],
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
