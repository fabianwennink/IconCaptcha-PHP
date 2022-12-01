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

    const VALID_ACTION_TYPES = ['LOAD', 'SELECTION'];

    /**
     * @var array $options
     */
    private array $options;

    private Challenge $challenge;

    private Validator $validator;

    public function __construct(array $options, Challenge $challenge, Validator $validator)
    {
        $this->options = $options;
        $this->challenge = $challenge;
        $this->validator = $validator;
    }

    public function isCaptchaRequest(): bool
    {
        return $this->isAjaxRequest() && !empty($_POST) && isset($_POST['payload']);
    }

    public function process(): void
    {
        if ($this->isCaptchaRequest()) {

            // Decode the payload.
            $payload = Payload::decode($_POST['payload']);

            // Validate the payload content.
            if (
                !isset($payload, $payload['action'], $payload['id'], $payload['timestamp'], $payload['initTimestamp']) || // ensure the payload is valid.
                !is_numeric($payload['id']) || // ensure the identifier is a number.
                !is_numeric($payload['timestamp']) || // ensure the timestamp is a number.
                !is_numeric($payload['initTimestamp']) || // ensure the initialization timestamp is a number.
                !in_array($payload['action'], self::VALID_ACTION_TYPES) // ensure the action type is known.
            ) {
                $this->badRequest();
            }

            // Validate the captcha token.
            if (!$this->validateToken($payload)) {
                $this->tokenError();
            }

            // Note: JS timestamps are in milliseconds.
            $currentTimestamp = Utils::getTimeInMilliseconds();
            $requestTimestamp = $payload['timestamp'];
            $scriptTimestamp = $payload['initTimestamp'];

            // Validate the payload timestamps.
            if(
                ($requestTimestamp > $currentTimestamp || $scriptTimestamp > $currentTimestamp) || // ensure the timestamps are older than the current time.
                ($scriptTimestamp > $requestTimestamp) // ensure the script init timestamp is older than the request timestamp.
            ) {
                $this->badRequest();
            }

            // Calculate the request latency. The value will be added to all
            // timeout timestamps, to compensate for latency time loss.
            $latency = $this->options['challenge']['latencyCorrection'] ? $currentTimestamp - $requestTimestamp : 0;

            switch ($payload['action']) {
                case 'LOAD':

                    // Validate the theme name. Fallback to light.
                    $theme = (isset($payload['theme']) && is_string($payload['theme'])) ? $payload['theme'] : 'light';

                    http_response_code(200);
                    header('Content-type: text/plain');
                    exit($this->challenge->initialize($payload['id'])->generate($theme, $latency));
                case 'SELECTION':

                    // Check if the captcha ID and required other payload data is set.
                    if (!isset($payload['x'], $payload['y'], $payload['width'])) {
                        $this->badRequest();
                    }

                    $challenge = $this->challenge->initialize($payload['id']);
                    $result = $challenge->makeSelection($payload['x'], $payload['y'], $payload['width'], $latency);

                    http_response_code(200);
                    header('Content-type: text/plain');
                    exit($result);
                default:
                    break;
            }
        }

        $this->badRequest();
    }

    /**
     * Validates the payload and possibly the header tokens.
     * @param array $payload The request payload to check.
     * @return bool TRUE if the token was valid, FALSE if it wasn't.
     */
    private function validateToken(array $payload): bool
    {
        $payloadToken = $payload['token'] ?? '';
        $headerToken = $_SERVER[self::CUSTOM_TOKEN_HEADER] ?? '';

        // Validate the tokens.
        return $this->validator->validateToken($payloadToken, $headerToken);
    }

    /**
     * Create a error message string for the token validation error.
     * @return void
     */
    private function tokenError(): void
    {
        http_response_code(200);
        header('Content-type: text/plain');
        exit(Payload::encode(['error' => 2]));
    }

    /**
     * Exits the request with a 400 bad request status.
     * @return void
     */
    private function badRequest(): void
    {
        http_response_code(400);
        exit;
    }

    /**
     * Checks if the current HTTP request was an Ajax request.
     * @return bool TRUE if it was an Ajax request, FALSE if it wasn't.
     */
    private function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }
}
