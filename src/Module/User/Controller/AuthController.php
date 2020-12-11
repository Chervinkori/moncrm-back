<?php

namespace App\Module\User\Controller;

use App\Component\Response\JsonResponse;
use App\Component\Token\JWT;
use App\Entity\User;
use App\Entity\UserSession;
use App\Hydrator\UserHydrator;
use App\Module\User\Service\UserSessionService;
use App\Repository\UserRepository;
use App\Repository\UserSessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Контроллер авторизации.
 *
 * @Route("/auth", name="auth_")
 *
 * @package App\Module\Auth\Controller
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class AuthController extends AbstractController
{
    /**
     * Регистрация пользователя в системе.
     *
     * @Route("/register", name="register", methods={"POST"})
     *
     * @param Request            $request
     * @param JsonResponse       $jsonResponse
     * @param ValidatorInterface $validator
     * @param UserHydrator       $userHydrator
     *
     * @return Response
     */
    public function register(
        Request $request,
        JsonResponse $jsonResponse,
        ValidatorInterface $validator,
        UserHydrator $userHydrator
    ): Response {
        $hydrator = $userHydrator->create();
        $user = $hydrator->hydrate($request->request->all(), new User());

        // Валидация данных
        $errors = $validator->validate($user, null, 'common');
        if ($errors->count()) {
            return $jsonResponse->error(null, $errors, $request);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        // TODO: ЖЦ ?

        return $jsonResponse->success(
            ['uuid' => $user->getUuid(), 'email' => $user->getEmail()],
            null,
            null,
            $request
        );
    }

    /**
     * Авторизация пользователя в системе.
     *
     * @IsGranted("IS_ANONYMOUS")
     * @Route("/login", name="login", methods={"POST"})
     *
     * @param Request                      $request
     * @param JsonResponse                 $jsonResponse
     * @param UserRepository               $userRepository
     * @param UserPasswordEncoderInterface $encoder
     * @param UserSessionService           $userSessionService
     * @param UserSessionRepository        $userSessionRepository
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function login(
        Request $request,
        JsonResponse $jsonResponse,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $encoder,
        UserSessionService $userSessionService,
        UserSessionRepository $userSessionRepository
    ): Response {
        // Получаем пользователя
        $user = $userRepository->findOneBy(['email' => $request->get('email')]);
        // Проверяем пользователя и валидность пароля
        if (!$user || !$encoder->isPasswordValid($user, $request->get('password'))) {
            return $jsonResponse->error('Неверный логин или пароль', null, $request);
        }

        // Удаляем просроченные сессии пользователя
        $userSessionService->deleteExpireSession($user);
        // Делаем поиск текущей сессии (по ip)
        // В теории их может быть несколько (это некорректно)
        $currentUserSessions = $userSessionRepository->getActiveSessions($user, $request->getClientIp());
        // Удаляем текущие сессии (чтобы не дублировать)
        $userSessionService->deleteSessions($currentUserSessions);
        // Создаём сессию пользователя
        $userSession = $userSessionService->createUserSession($user, $request->getClientIp());

        // Генерируем токен доступа
        $accessToken = JWT::encode(
            ['uuid' => $user->getUuid()],
            $this->getParameter('app_secret'),
            $this->getParameter('access_token_lifetime')
        );

        // Создаём куку для рефреш токена (сессии)
        $userSessionCookie = $userSessionService->createUserSessionCookie($userSession);

        return $jsonResponse->success(
            ['access_token' => $accessToken, 'refresh_token' => $userSession->getUuid(), 'token_type' => 'bearer'],
            null,
            null,
            $request,
            null,
            ['set-cookie' => [$userSessionCookie]]
        );
    }

    /**
     * Обновление токена доступа.
     *
     * @Route("/refresh-token", name="refresh-token", methods={"POST"})
     *
     * @param Request               $request
     * @param JsonResponse          $jsonResponse
     * @param UserSessionRepository $userSessionRepository
     * @param UserSessionService    $userSessionService
     *
     * @return Response
     * @throws \Exception
     * @api
     */
    public function refreshToken(
        Request $request,
        JsonResponse $jsonResponse,
        UserSessionRepository $userSessionRepository,
        UserSessionService $userSessionService
    ): Response {
        if (!$request->cookies->has('refresh_token')) {
            return $jsonResponse->error('Отсутствует токен обновления доступа');
        }

        // Получаем из кук рефреш токен
        $refreshToken = $request->cookies->get('refresh_token');

        /** @var UserSession $userSession */
        $userSession = $userSessionRepository->find($refreshToken);
        if (!$userSession) {
            return $jsonResponse->error(
                'Сессия пользователя не найдена',
                null,
                $request,
                null,
                ['refreshToken' => $refreshToken]
            );
        }

        // Проверяем что рефреш токен не украден.
        // Т.е. ip пользователя на момент создания токена и сейчас совпадают
        // TODO: дописать проверку fingerprint
        if ($userSession->getIp() != $request->getClientIp()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userSession);
            $em->flush();

            // TODO: логирование?
            return $jsonResponse->error('Сессия пользователя не найдена', null, $request);
        }

        // Получаем пользователя из сессии, т.к. дальше сессия удаляется
        $user = $userSession->getUser();

        // Удаляем текущую сессии (чтобы не дублировать)
        $userSessionService->deleteSessions($userSession);
        // Создаем сессию пользователя
        $userSession = $userSessionService->createUserSession($user, $request->getClientIp());

        // Генерируем токен доступа
        $accessToken = JWT::encode(
            ['uuid' => $userSession->getUser()->getUuid()],
            $this->getParameter('app_secret'),
            $this->getParameter('access_token_lifetime')
        );

        // Создаём куку для рефреш токена (сессии)
        $userSessionCookie = $userSessionService->createUserSessionCookie($userSession);

        return $jsonResponse->success(
            ['access_token' => $accessToken, 'refresh_token' => $userSession->getUuid(), 'token_type' => 'bearer'],
            null,
            null,
            $request,
            null,
            ['set-cookie' => [$userSessionCookie]]
        );
    }
}
