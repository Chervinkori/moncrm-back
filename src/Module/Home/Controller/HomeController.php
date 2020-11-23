<?php

namespace App\Module\Home\Controller;

use App\Component\Response\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 *
 * @package App\Module\Home\Controller
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param JsonResponse $response
     *
     * @return Response
     */
    public function home(JsonResponse $response): Response
    {
        return $response->success(null, 'Home page');
    }
}
