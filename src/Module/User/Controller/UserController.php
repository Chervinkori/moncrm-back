<?php

namespace App\Module\User\Controller;

use App\Component\Response\JsonResponse;
use App\Hydrator\UserHydrator;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserController
 *
 * @Route("/user", name="user_")
 *
 * @package App\Module\User\Controller
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class UserController
{
    /**
     * Отдаёт данные пользователя.
     *
     * @Route("/get/{uuid}", name="get", methods={"GET"})
     *
     * @IsGranted
     *
     * @param string         $uuid Идентификатор пользователя.
     * @param Request        $request
     * @param UserRepository $userRepository
     * @param UserHydrator   $userHydrator
     * @param JsonResponse   $jsonResponse
     *
     * @return Response
     */
    public function get(
        string $uuid,
        Request $request,
        UserRepository $userRepository,
        UserHydrator $userHydrator,
        JsonResponse $jsonResponse
    ): Response {
        $violations = Validation::createValidator()->validate($uuid, new Assert\Uuid());
        if ($violations->count()) {
            return $jsonResponse->error('Некорректный идентификатор пользователя');
        }

        $user = $userRepository->find($uuid);
        if (!$user) {
            return $jsonResponse->error('Пользователь не найден', null, $request, Response::HTTP_NOT_FOUND);
        }

        return $jsonResponse->success($userHydrator->create()->extract($user), null, null, [$request, 'uuid' => $uuid]);
    }
}
