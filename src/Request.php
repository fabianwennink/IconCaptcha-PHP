<?php

/**
 * IconCaptcha Plugin: v3.1.0
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

namespace IconCaptcha;

use IconCaptcha\Challenge\Challenge;
use IconCaptcha\Challenge\Validator;

class Request
{
    const CUSTOM_TOKEN_HEADER = 'HTTP_X_ICONCAPTCHA_TOKEN';

    private $challenge;

    private $validator;

    public function __construct(Challenge $challenge, Validator $validator)
    {
        $this->challenge = $challenge;
        $this->validator = $validator;
    }

    public function isCaptchaRequest()
    {
        return $this->isAjaxRequest() && !empty($_POST) && isset($_POST['payload']);
    }

    public function process()
    {
        if ($this->isCaptchaRequest()) {

            // Decode the payload.
            $payload = $this->decodePayload($_POST['payload']);

            // Validate the payload content.
            if (!isset($payload, $payload['action'], $payload['id']) || !is_string($payload['action']) || !is_numeric($payload['id'])) {
                $this->badRequest();
            }

            // Validate the captcha token.
            if (!$this->validateToken($payload, true)) {
                $this->tokenError();
            }

            $identifier = $payload['id'];

            switch ($payload['action']) {
                case 'LOAD':

                    // Validate the theme name. Fallback to light.
                    $theme = (isset($payload['theme']) && is_string($payload['theme'])) ? $payload['theme'] : 'light';

                    // Echo the captcha data.
                    http_response_code(200);
                    header('Content-type: text/plain');
                    exit($this->challenge->initialize($identifier)->generate($theme));
                case 'SELECTION':

                    // Check if the captcha ID and required other payload data is set.
                    if (!isset($payload['x'], $payload['y'], $payload['width'])) {
                        $this->badRequest();
                    }

                    if ($this->challenge->initialize($identifier)->makeSelection($payload['x'], $payload['y'], $payload['width'])) {
                        http_response_code(200);
                        exit;
                    }
                    break;
                case 'INVALIDATE':
                    $this->validator->invalidate($payload['id']);
                    http_response_code(200);
                    exit;
                default:
                    break;
            }
        }

        $this->badRequest();
    }

    /**
     * Exits the request with a 400 bad request status.
     * @return void
     */
    public function badRequest()
    {
        http_response_code(400);
        exit;
    }

    /**
     * Validates the payload and possibly the header tokens.
     * @param array $payload The request payload to check.
     * @param bool $checkHeader If the presence of the custom HTTP header should be checked.
     * @return bool TRUE if the token was valid, FALSE if it wasn't.
     */
    private function validateToken($payload, $checkHeader)
    {
        $payloadToken = null;
        $headerToken = null;

        // Get the token from the payload.
        if (!empty($payload['token'])) {
            $payloadToken = $payload['token'];
        }

        // Get the token from the request headers, can be empty for some requests.
        if ($checkHeader && !empty($_SERVER[self::CUSTOM_TOKEN_HEADER])) {
            $headerToken = $_SERVER[self::CUSTOM_TOKEN_HEADER];
        }

        // Validate the tokens.
        return $this->validator->validateToken($payloadToken, $headerToken);
    }

    /**
     * Tries to decode the given base64 and json encoded payload.
     * @param string $payload The request payload to be decoded.
     * @return mixed The decoded payload.
     */
    private function decodePayload($payload)
    {
        // Base64 decode the payload.
        $payload = base64_decode($payload);
        if ($payload === false) {
            $this->badRequest();
        }

        // JSON decode the payload.
        return json_decode($payload, true);
    }

    /**
     * Create a error message string for the token validation error.
     * @return void
     */
    private function tokenError()
    {
        http_response_code(200);
        header('Content-type: text/plain');
        exit(base64_encode(json_encode(['error' => 2])));
    }

    /**
     * Checks if the current HTTP request was an Ajax request.
     * @return bool TRUE if it was an Ajax request, FALSE if it wasn't.
     */
    private function isAjaxRequest()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }
}
