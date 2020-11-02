<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as AController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Абстрактный базовый класс
 *
 * Class AbstractController
 * @package App\Controller
 */
abstract class AbstractController extends AController
{
    /**
     * @param array|null $data
     * @param array|null $meta
     * @param int $status
     * @param array $headers
     * @param array $context
     * @return JsonResponse
     */
    protected function jsonSuccess(
        array $data = null,
        array $meta = null,
        int $status = Response::HTTP_OK,
        array $headers = [],
        array $context = []
    ): JsonResponse {
        return $this->json(['data' => $data, 'meta' => $meta], $status, $headers, $context);
    }

    /**
     * @param string $title
     * @param array|null $detail
     * @param int $status
     * @param array $headers
     * @param array $context
     * @return JsonResponse
     */
    protected function jsonError(
        string $title,
        array $detail = null,
        int $status = Response::HTTP_BAD_REQUEST,
        array $headers = [],
        array $context = []
    ): JsonResponse {
        return $this->json(['error' => ['title' => $title, 'detail' => $detail]], $status, $headers, $context);
    }
}
