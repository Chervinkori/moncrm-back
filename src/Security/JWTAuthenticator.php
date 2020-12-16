<?php

namespace App\Security;

use App\Component\Response\JsonResponse;
use App\Component\Token\JWT;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Аутентификатор по JWT.
 *
 * @package App\Security
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class JWTAuthenticator extends AbstractAuthenticator
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
     * Проверяет поддерживает ли аутентификатор данный запрос.
     *
     * @param Request $request
     *
     * @return bool|null
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization')
            && preg_match('/Bearer\s(\S+)/', $request->headers->get('Authorization'));
    }

    /**
     * Аутентификация пользователя.
     *
     * @param Request $request
     *
     * @return PassportInterface
     */
    public function authenticate(Request $request): PassportInterface
    {
        // Получает токен доступа
        $jwt = str_replace('Bearer ', '', $request->headers->get('Authorization'));
        // Декодирует и проверяет токен
        try {
            $payload = JWT::decode($jwt, $this->params->get('app_secret'), 'HS256');
        } catch (\Exception $exp) {
            throw new CustomUserMessageAuthenticationException('Ошибка декодирования JWT: ' . $exp->getMessage());
        }

        // Проверяет наличие идентификатора пользователя
        if (empty($payload['uuid'])) {
            throw new CustomUserMessageAuthenticationException('В токене не найден идентификатор пользователя');
        }

        $user = $this->userRepository->find($payload['uuid']);
        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Пользователь не найден');
        }

        return new SelfValidatingPassport($user);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * Вызывается, когда аутентификация выполнена, но не удалась (например, неверный пароль для имени пользователя).
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->jsonResponse->error(
            'Ошибка авторизации пользователя',
            null,
            [$request, $exception],
            Response::HTTP_UNAUTHORIZED
        );
    }
}
