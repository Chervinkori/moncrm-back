<?php

namespace App\Module\Auth\Controller;

use App\Component\Response\JsonResponse;
use App\Component\Token\JWT;
use App\Component\Validator\Validator;
use App\Entity\User;
use App\Entity\UserSession;
use App\Hydrator\UserHydratorBuilder;
use App\Module\Auth\Service\UserSessionService;
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
 * @Route("/auth", name="auth_")
 *
 * Class AuthController
 * @package App\Controller
 */
class AuthController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"POST"})
     *
     * @param Request $request
     * @param JsonResponse $jsonResponse
     * @param UserHydratorBuilder $userHydratorBuilder
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function register(
        Request $request,
        JsonResponse $jsonResponse,
        UserHydratorBuilder $userHydratorBuilder,
        ValidatorInterface $validator
    ): Response {
        $hydrator = $userHydratorBuilder->build();
        $user = $hydrator->hydrate($request->request->all(), new User());

        $errors = $validator->validate($user, null, 'common');
        if (count($errors) > 0) {
            return $jsonResponse->createResponseBuilder(false)->withValidationError($errors)->build();
//            return new JsonResponse($responseData->addValidationErrors($errors)->toArray(), Response::HTTP_BAD_REQUEST);
        }
//
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($user);
//        $em->flush();
//
//        // TODO: ЖЦ ?
//
//        return new JsonResponse(
//            $responseData->setData(['uuid' => $user->getUuid(), 'email' => $user->getEmail()])->toArray()
//        );
    }

    /**
     * @IsGranted("IS_ANONYMOUS")
     * @Route("/login", name="login", methods={"POST"})
     *
     * @param Request $request
     * @param ResponseData $responseData
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $encoder
     * @param UserSessionService $userSessionService
     * @param UserSessionRepository $userSessionRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function login(
        Request $request,
        ResponseData $responseData,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $encoder,
        UserSessionService $userSessionService,
        UserSessionRepository $userSessionRepository
    ): JsonResponse {
        // Получаем пользователя
        $user = $userRepository->findOneBy(['email' => $request->get('email')]);
        // Проверяем пользователя и валидность пароля
        if (!$user || !$encoder->isPasswordValid($user, $request->get('password'))) {
            return new JsonResponse(
                $responseData->addError('Неверный логин или пароль')->toArray(),
                Response::HTTP_BAD_REQUEST
            );
        }

        // Удаляем просроченные сессии пользователя
        $userSessionService->deleteExpireSession($user);
        // Делаем поиск текущей сессии (по ip)
        // В теории их может быть несколько (это некорректно)
        $currentUserSessions = $userSessionRepository->getActiveSessions($user, $request->getClientIp());
        // Удаляем текущие сессии (чтобы не дублировать)
        $userSessionService->deleteSessions($currentUserSessions);
        // Создаем сессию пользователя
        $userSession = $userSessionService->createUserSession($user, $request->getClientIp());

        // Генерируем токен доступа
        $accessToken = JWT::encode(
            ['uuid' => $user->getUuid()],
            $this->getParameter('app_secret'),
            $this->getParameter('access_token_lifetime')
        );

        // Создаем куку для рефреш токена (сессии)
        $userSessionCookie = $userSessionService->createUserSessionCookie($userSession);

        return new JsonResponse(
            $responseData->setData(
                ['access_token' => $accessToken, 'refresh_token' => $userSession->getUuid(), 'token_type' => 'bearer']
            )->toArray(),
            Response::HTTP_OK,
            ['set-cookie' => [$userSessionCookie]]
        );
    }

    /**
     * @Route("/refresh-token", name="refresh-token", methods={"POST"})
     *
     * @param Request $request
     * @param ResponseData $responseData
     * @param UserSessionRepository $userSessionRepository
     * @param UserSessionService $userSessionService
     * @return JsonResponse
     * @throws \Exception
     */
    public function refreshToken(
        Request $request,
        ResponseData $responseData,
        UserSessionRepository $userSessionRepository,
        UserSessionService $userSessionService
    ): JsonResponse {
        if (!$request->cookies->has('refresh_token')) {
            return new JsonResponse(
                $responseData->addError('Отсутствует токен обновления доступа')->toArray(),
                Response::HTTP_BAD_REQUEST
            );
        }

        // Получаем из кук рефреш токен
        $refreshToken = $request->cookies->get('refresh_token');

        /** @var UserSession $userSession */
        $userSession = $userSessionRepository->find($refreshToken);
        if (!$userSession) {
            return new JsonResponse(
                $responseData->addError('Сессия пользователя не найдена')->toArray(),
                Response::HTTP_BAD_REQUEST
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
            return new JsonResponse(
                $responseData->addError('Сессия пользователя не найдена')->toArray(),
                Response::HTTP_BAD_REQUEST
            );
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

        // Создаем куку для рефреш токена (сессии)
        $userSessionCookie = $userSessionService->createUserSessionCookie($userSession);

        return new JsonResponse(
            $responseData->setData(
                ['access_token' => $accessToken, 'refresh_token' => $userSession->getUuid(), 'token_type' => 'bearer']
            )->toArray(),
            Response::HTTP_OK,
            ['set-cookie' => [$userSessionCookie]]
        );
    }
}
