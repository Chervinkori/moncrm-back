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
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
        $violations = $validator->validate($user, null, 'main');
        if ($violations->count()) {
            return $jsonResponse->error(null, $violations, $request);
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
     * @Route("/sign-in", name="signIn", methods={"POST"})
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
    public function signIn(
        Request $request,
        JsonResponse $jsonResponse,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $encoder,
        UserSessionService $userSessionService,
        UserSessionRepository $userSessionRepository
    ): Response {
        // Получает пользователя
        $user = $userRepository->findOneBy(['email' => $request->get('email')]);
        // Проверяет пользователя и валидность пароля
        if (!$user || !$encoder->isPasswordValid($user, $request->get('password'))) {
            return $jsonResponse->error('Неверный логин или пароль', null, $request);
        }

        // Удаляет просроченные сессии пользователя
        $userSessionService->deleteExpireSession($user);
        // Поиск текущей сессии (по ip)
        // В теории их может быть несколько (это некорректно)
        $currentUserSessions = $userSessionRepository->getActiveSessions($user, $request->getClientIp());
        // Удаляет текущие сессии (чтобы не дублировать)
        $userSessionService->deleteSessions($currentUserSessions);
        // Создаём сессию пользователя
        $userSession = $userSessionService->createUserSession($user, $request->getClientIp());

        // Генерирует токен доступа
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
     * @Route("/refresh-access-token", name="refreshAccessToken", methods={"POST"})
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
    public function refreshAccessToken(
        Request $request,
        JsonResponse $jsonResponse,
        UserSessionRepository $userSessionRepository,
        UserSessionService $userSessionService
    ): Response {
        // Получает из кук рефреш токен
        $refreshToken = $request->cookies->get('refresh_token');
        if (!$refreshToken) {
            return $jsonResponse->error('Отсутствует токен обновления доступа');
        }

        // Валидация токена
        $violations = Validation::createValidator()->validate($refreshToken, new Assert\Uuid());
        if ($violations->count()) {
            // TODO: залогировать
            return $jsonResponse->error('Отсутствует токен обновления доступа');
        }

        /** @var UserSession $userSession */
        $userSession = $userSessionRepository->find($refreshToken);
        if (!$userSession) {
            $response = $jsonResponse->error('Сессия пользователя не найдена', null, $request);
            // Удаляет токен обновления доступа из cookie
            $response->headers->clearCookie('refresh_token', '/backend/auth');

            return $response;
        }

        // Если сессия просрочена
        if ($userSession->getExp() < new \DateTime()) {
            // Удаляет сессию пользователя
            $em = $this->getDoctrine()->getManager();
            $em->remove($userSession);
            $em->flush();

            $response = $jsonResponse->error('Сессия пользователя истекла', null, $request);
            // Удаляет токен обновления доступа из cookie
            $response->headers->clearCookie('refresh_token', '/backend/auth');

            return $response;
        }

        // Проверяет что рефреш токен не украден.
        // Т.е. ip пользователя на момент создания токена и сейчас совпадают
        // TODO: дописать проверку fingerprint
        if ($userSession->getIp() != $request->getClientIp()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($userSession);
            $em->flush();

            // TODO: залогировать

            $response = $jsonResponse->error('Сессия пользователя не найдена', null, $request);
            // Удаляет токен обновления доступа из cookie
            $response->headers->clearCookie('refresh_token', '/backend/auth');

            return $response;
        }

        // Получает пользователя из сессии, т.к. дальше сессия удаляется
        $user = $userSession->getUser();

        // Удаляет текущую сессии (чтобы не дублировать)
        $userSessionService->deleteSessions($userSession);
        // Создаёт сессию пользователя
        $userSession = $userSessionService->createUserSession($user, $request->getClientIp());

        // Генерирует токен доступа
        $accessToken = JWT::encode(
            ['uuid' => $userSession->getUser()->getUuid()],
            $this->getParameter('app_secret'),
            $this->getParameter('access_token_lifetime')
        );

        // Создаёт куку для рефреш токена (сессии)
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
     * @Route("/sign-out", name="signOut", methods={"GET"})
     *
     * @param Request               $request
     * @param UserSessionRepository $userSessionRepository
     * @param JsonResponse          $jsonResponse
     *
     * @return Response
     */
    public function signOut(
        Request $request,
        UserSessionRepository $userSessionRepository,
        JsonResponse $jsonResponse
    ): Response {
        $response = $jsonResponse->success();

        // Получает из кук рефреш токен
        $refreshToken = $request->cookies->get('refresh_token');
        if ($refreshToken) {
            // Валидация токена
            $violations = Validation::createValidator()->validate($refreshToken, new Assert\Uuid());
            // Если токен валидный - делает поиск в сессиях пользователей
            if (!$violations->count()) {
                /** @var UserSession $userSession */
                $userSession = $userSessionRepository->find($refreshToken);
                if ($userSession) {
                    // Удаляет текущую сессии (чтобы не дублировать)
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($userSession);
                    $em->flush();
                }
            }

            // Удаляет токен обновления доступа из cookie
            $response->headers->clearCookie('refresh_token', '/backend/auth');
        }

        return $response;
    }
}
