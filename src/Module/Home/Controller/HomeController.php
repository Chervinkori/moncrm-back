<?php

namespace App\Module\Home\Controller;

use App\Component\Response\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class HomeController
 *
 * @package App\Module\Home\Controller
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="open-home")
     *
     * @param JsonResponse $response
     *
     * @return Response
     */
    public function openHome(JsonResponse $response): Response
    {
        return $response->success(null, 'Open home page');
    }

    /**
     * @Route("/", name="close-home")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     *
     * @param JsonResponse $response
     *
     * @return Response
     */
    public function closeHome(JsonResponse $response): Response
    {
        return $response->success(null, 'Close home page');
    }
}
