<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Token;

interface TokenInterface
{
    /**
     * Returns an HTML input field as a string, containing the captcha token as its value.
     *
     * @return string The HTML input field.
     */
    public static function render(): string;

    /**
     * Returns the captcha token for the current request.
     *
     * @return string The captcha token.
     */
    public function get(): string;

    /**
     * Validates the global captcha token against the given payload token and sometimes against a header token
     * as well. All the given tokens must match the global captcha token to pass the check.
     *
     * @param string $payloadToken The token string received via the HTTP request body.
     * @param string|null $headerToken The token string received via the HTTP request headers. This value
     * is optional,  as not every request will contain custom HTTP headers and thus this token should be able
     * to be skipped. Default value is NULL. When the value is set to anything else other than NULL, the given
     * value will be checked against the captcha token.
     * @return bool TRUE if the captcha token matches the given tokens, FALSE if it does not match.
     */
    public function validate(string $payloadToken, string $headerToken = null): bool;
}
