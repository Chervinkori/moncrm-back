<?php

namespace App\Module\User\Controller;

use App\Module\User\Hydrator\UserHydratorBuilder;
use App\Component\Response\JsonResponse;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class UserController
 *
 * @Route("/user", name="user_")
 *
 * @package App\Module\User\Controller
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class UserController extends AbstractController
{
    /**
     * Отдаёт данные пользователя.
     *
     * @Route("/get/{uuid}", name="get", methods={"GET"})
     *
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     *
     * @param string              $uuid Идентификатор пользователя.
     * @param Request             $request
     * @param UserRepository      $userRepository
     * @param UserHydratorBuilder $userHydratorBuilder
     * @param JsonResponse        $jsonResponse
     *
     * @return Response
     */
    public function getData(
        string $uuid,
        Request $request,
        UserRepository $userRepository,
        UserHydratorBuilder $userHydratorBuilder,
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

        // Если запрашиваю персональные данные (т.е. пользователь запрашивает свои же данные), сохраняет время запроса
        if ($request->get('personal')) {
            $user->setPersonalRequestedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
        }

        $hydrator = $userHydratorBuilder->build();

        // TODO
        // $hydrator->addFilter('removeFields', new RemoveFieldFilter(['password']));

        $data = $hydrator->extract($user);

        return $jsonResponse->success($data, null, null, [$request, 'uuid' => $uuid]);
    }
}
