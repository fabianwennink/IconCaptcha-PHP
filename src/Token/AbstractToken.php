<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Token;

use Exception;

abstract class AbstractToken
{
    /**
     * The default length of a captcha token.
     */
    public const TOKEN_LENGTH = 32;

    /**
     * The name of the form input field containing the captcha token.
     */
    public const TOKEN_FIELD_NAME = '_iconcaptcha-token';

    /**
     * Generates a token string, which will serve as a CSRF token. Based on the PHP version and
     * installed extensions, different internal PHP functions will be used to generate the token.
     *
     * @return string The generated token.
     */
    protected function generate(): string
    {
        // Create a secure captcha session token.
        try {
            $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        } catch (Exception $e) {
            // Using a fallback in case of an exception.
            if (function_exists('openssl_random_pseudo_bytes')) {
                // Only available when the OpenSSL extension is installed.
                $token = bin2hex(openssl_random_pseudo_bytes(self::TOKEN_LENGTH));
            }
            // If the OpenSSL extension is not installed, use this fallback.
            $token = $token ?? str_shuffle(md5(uniqid(mt_rand(), true)));
        }

        return $token;
    }

    /**
     * Checks if the given token (correct token) matches the token values passed to the request as part of the payload and headers.
     * The payload token is always required, while the header token might not be present with every request. For that reason, it will
     * only be compared against the correct token if it's present. It not being present, is no reason for the check to return FALSE.
     *
     * @param $storedToken string The correct token, which will be compared against the payload & header tokens.
     * @param $payloadToken string The token sent with the request as part of the payload.
     * @param $headerToken string|null The token sent with the request as a header. Can be empty in certain requests.
     * @return bool TRUE if the token(s) match the stored token, FALSE if it/they don't.
     */
    protected function compareToken(string $storedToken, string $payloadToken, string $headerToken = null): bool
    {
        // If the token is empty, the token was never requested.
        if (empty($storedToken)) {
            return false;
        }

        // Validate the payload and header token (if set) against the stored token.
        if ($headerToken !== null) {
            return $storedToken === $payloadToken && $storedToken === $headerToken;
        }

        return $storedToken === $payloadToken;
    }
}
