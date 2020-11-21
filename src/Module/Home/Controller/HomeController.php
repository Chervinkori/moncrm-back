<?php

namespace App\Module\Home\Controller;

use App\Component\Response\JsonResponse;
use App\Utility\ResponseUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(JsonResponse $response): Response
    {
        return $response->success(null, 'Home page :)');
    }
}
