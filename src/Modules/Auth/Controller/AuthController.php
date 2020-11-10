<?php

namespace App\Modules\Auth\Controller;

use App\Entity\User;
use App\Entity\UserSession;
use App\Modules\Auth\Service\UserSessionService;
use App\Repository\UserRepository;
use App\Repository\UserSessionRepository;
use App\Utility\JWTUtils;
use App\Utility\ResponseUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/auth", name="auth_")
 *
 * Class AuthController
 * @package App\Controller
 */
class AuthController extends AbstractController
{
//    /**
//     * @IsGranted("IS_ANONYMOUS")
//     * @Route("/register", name="register", methods={"POST"})
//     *
//     * @param Request $request
//     * @param UserRepository $userRepository
//     * @param UserPasswordEncoderInterface $encoder
//     * @return JsonResponse
//     */
//    public function register(
//        Request $request,
//        UserRepository $userRepository,
//        UserPasswordEncoderInterface $encoder
//    ): JsonResponse {
//        $password = $request->get('password');
//        $email = $request->get('email');
//
//        // Проверяем уникальность почты
//        $user = $userRepository->findOneBy(['email' => $email]);
//        if (!$user) {
//            return ResponseUtils::jsonError('Пользователь с таким адресом электронной почты уже существует');
//        }
//
//        // Создаем пользователя
//        $user = new User();
//        $user->setPassword($encoder->encodePassword($user, $password));
//        $user->setEmail($email);
//
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($user);
//        $em->flush();
//
//        // TODO: ЖЦ
//
//        return ResponseUtils::jsonSuccess(['uuid' => $user->getUuid(), 'email' => $user->getEmail()]);
//    }

    /**
     * @IsGranted("IS_ANONYMOUS")
     * @Route("/login", name="login", methods={"POST"})
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $encoder
     * @param UserSessionService $userSessionService
     * @param UserSessionRepository $userSessionRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $encoder,
        UserSessionService $userSessionService,
        UserSessionRepository $userSessionRepository
    ): JsonResponse {
        // Получаем пользователя
        $user = $userRepository->findOneBy(['email' => $request->get('email')]);
        // Проверяем пользователя и валидность пароля
        if (!$user || !$encoder->isPasswordValid($user, $request->get('password'))) {
            return ResponseUtils::jsonError('Неверный логин или пароль');
        }

        // Удаляем просроченные сессии пользователя
        $userSessionService->deleteExpireSession($user);
        // Делаем поиск текущей сессии
        // В теории их может быть несколько (это некорректно)
        $currentUserSessions = $userSessionRepository->getActiveSessions($user, $request->getClientIp());
        // Удаляем текущие сессии (чтобы не дублировать)
        $userSessionService->deleteSessions($currentUserSessions);
        // Создаем сессию пользователя
        $userSession = $userSessionService->createUserSession($user, $request->getClientIp());

        // Генерируем токен доступа
        $accessToken = JWTUtils::encode(
            ['uuid' => $user->getUuid()],
            $this->getParameter('app_secret'),
            $this->getParameter('access_token_lifetime')
        );

        // Создаем куку для рефреш токена (сессии)
        $userSessionCookie = $userSessionService->createUserSessionCookie($userSession);

        return ResponseUtils::jsonSuccess(
            ['access_token' => $accessToken, 'refresh_token' => $userSession->getUuid(), 'token_type' => 'bearer'],
            null,
            Response::HTTP_OK,
            ['set-cookie' => [$userSessionCookie]]
        );
    }

    /**
     * @Route("/refresh-token", name="refresh-token", methods={"POST"})
     *
     * @param Request $request
     * @param UserSessionRepository $userSessionRepository
     * @param UserSessionService $userSessionService
     * @return JsonResponse
     * @throws \Exception
     */
    public function refreshToken(
        Request $request,
        UserSessionRepository $userSessionRepository,
        UserSessionService $userSessionService
    ): JsonResponse {
        if (!$request->cookies->has('refresh_token')) {
            return ResponseUtils::jsonError('Отсутствует токен обновления доступа');
        }

        // Получаем из кук рефреш токен
        $refreshToken = $request->cookies->get('refresh_token');

        /** @var UserSession $userSession */
        $userSession = $userSessionRepository->find($refreshToken);
        if (!$userSession) {
            return ResponseUtils::jsonError('Сессия пользователя не найдена');
        }

        // Проверяем что рефреш токен не украден.
        // Т.е. ip пользователя на момент создания токена и сейчас совпадают
        // TODO: дописать проверку fingerprint
        if ($userSession->getIp() != $request->getClientIp()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userSession);
            $em->flush();

            // TODO: логирование?

            return ResponseUtils::jsonError('Сессия пользователя не найдена');
        }

        // Получаем пользователя из сессии, т.к. дальше сессия удаляется
        $user = $userSession->getUser();

        // Удаляем текущую сессии (чтобы не дублировать)
        $userSessionService->deleteSessions($userSession);
        // Создаем сессию пользователя
        $userSession = $userSessionService->createUserSession($user, $request->getClientIp());

        // Генерируем токен доступа
        $accessToken = JWTUtils::encode(
            ['uuid' => $userSession->getUser()->getUuid()],
            $this->getParameter('app_secret'),
            $this->getParameter('access_token_lifetime')
        );

        // Создаем куку для рефреш токена (сессии)
        $userSessionCookie = $userSessionService->createUserSessionCookie($userSession);

        return ResponseUtils::jsonSuccess(
            ['access_token' => $accessToken, 'refresh_token' => $userSession->getUuid(), 'token_type' => 'bearer'],
            null,
            Response::HTTP_OK,
            ['set-cookie' => [$userSessionCookie]]
        );
    }
}
