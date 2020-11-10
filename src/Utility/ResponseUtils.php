<?php

namespace App\Utility;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResponsUtils
 * @package App\Utility
 */
class ResponseUtils
{
    /**
     * @param null $data
     * @param array|null $meta
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public static function jsonSuccess(
        $data = null,
        array $meta = null,
        int $status = Response::HTTP_OK,
        array $headers = []
    ): JsonResponse {
        $data = [
            'meta' => $meta,
            'data' => $data
        ];

        return new JsonResponse($data, $status, $headers);
    }

    /**
     * @param string $title
     * @param null $detail
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public static function jsonError(
        string $title,
        $detail = null,
        int $status = Response::HTTP_BAD_REQUEST,
        array $headers = []
    ): JsonResponse {
        $error = [
            'error' => [
                'title' => $title,
                'detail' => $detail,
            ]
        ];

        return new JsonResponse($error, $status, $headers);
    }
}
