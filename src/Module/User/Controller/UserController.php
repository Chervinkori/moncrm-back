<?php

namespace App\Module\User\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
class UserController
{
    /**
     *
     * @Route("/get/{uuid}", name="get", methods={"GET"})
     *
     * @param string  $uuid
     * @param Request $request
     *
     * @return Response
     */
    public function get(string $uuid, Request $request): Response
    {
    }
}
