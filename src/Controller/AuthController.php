<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $password = $request->get('password');
        $email = $request->get('email');

        $user = new User();
        $user->setPassword($encoder->encodePassword($user, $password));
        $user->setEmail($email);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        // TODO: ЖЦ

        return $this->jsonSuccess(['uuid' => $user->getUuid(), 'email' => $user->getEmail()]);
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $encoder
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $encoder,
        TranslatorInterface $translator
    ): JsonResponse {
        $user = $userRepository->findOneBy(['email' => $request->get('email')]);
        if (!$user || !$encoder->isPasswordValid($user, $request->get('password'))) {
            return $this->jsonError($translator->trans('invalid.credentials'), null, Response::HTTP_UNAUTHORIZED);
        }

        $date = new \DateTime;
        $jwt = JWT::encode(
            [
                'iat' => $date->getTimestamp(),
                'exp' => $date->modify('+2 hour')->getTimestamp(),
                'iss' => 'MonCRM', // Надо?
                'uuid' => $user->getUuid()
            ],
            $this->getParameter('app_secret'),
            'HS256'
        );

        return $this->jsonSuccess(['access_token' => $jwt, 'token_type' => 'bearer']);
    }
}
