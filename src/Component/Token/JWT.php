<?php

namespace App\Component\Token;

/**
 * Компонент создания JWT.
 *
 * @package App\Component\Token
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class JWT
{
    /**
     * Создание токена.
     *
     * @param array    $payload  PHP object or array.
     * @param string   $key      The secret key.
     * @param int|null $lifetime Время жизни в секундах.
     * @param string   $alg      The signing algorithm.
     * @param null     $head     An array with header elements to attach.
     *
     * @return string Готовый JWT.
     */
    public static function encode(
        array $payload,
        $key,
        int $lifetime = null,
        string $alg = 'HS256',
        $head = null
    ): string {
        $date = new \DateTime;
        $payload = array_merge(
            [
                'iss' => 'MonCRM',
                'iat' => $date->getTimestamp()
            ],
            $payload
        );
        if ($lifetime) {
            $payload['exp'] = $date->modify('+' . $lifetime . ' second')->getTimestamp();
        }

        return \Firebase\JWT\JWT::encode(
            $payload,
            $key,
            $alg,
            null,
            $head
        );
    }

    /**
     * Проверить и декодировать токен.
     *
     * @param string                $jwt          The JWT.
     * @param string|array|resource $key          The key, or map of keys.
     * @param string|array          $allowed_algs List of supported verification algorithms.
     *
     * @return array The JWT's payload as a PHP array.
     */
    public static function decode(string $jwt, $key, $allowed_algs): array
    {
        return (array)\Firebase\JWT\JWT::decode(
            $jwt,
            $key,
            (is_array($allowed_algs) ? $allowed_algs : [$allowed_algs])
        );
    }
}
