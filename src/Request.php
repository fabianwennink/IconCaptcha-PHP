<?php

namespace IconCaptcha;

use IconCaptcha\Challenge\Challenge;
use IconCaptcha\Challenge\Validator;
use JsonException;

class Request
{
    /**
     * The HTTP header name which contains the captcha token.
     */
    private const CUSTOM_TOKEN_HEADER = 'HTTP_X_ICONCAPTCHA_TOKEN';

    /**
     * List of allowed action types.
     */
    private const VALID_ACTION_TYPES = ['LOAD', 'SELECTION'];

    /**
     * @var Challenge The challenge generator.
     */
    private Challenge $challenge;

    /**
     * @var Validator The challenge validator.
     */
    private Validator $validator;

    /**
     * Initializes a new captcha request handler instance.
     *
     * @param Challenge $challenge The challenge generator.
     * @param Validator $validator The challenge validator.
     */
    public function __construct(Challenge $challenge, Validator $validator)
    {
        $this->challenge = $challenge;
        $this->validator = $validator;
    }

    /**
     * Returns whether the current request is a valid captcha request.
     */
    public function isCaptchaRequest(): bool
    {
        return $this->isAjaxRequest() && !empty($_POST) && isset($_POST['payload']);
    }

    /**
     * Processes the captcha request and performs actions based on the request payload.
     */
    public function process(): void
    {
        // Validate the request.
        if (!$this->isCaptchaRequest()) {
            $this->badRequest();
            return;
        }

        // Decode the payload.
        try {
            $payload = Payload::decode($_POST['payload']);
        } catch (JsonException $e) {
            $this->badRequest();
            return;
        }

        // Validate the payload content.
        if (
            !isset($payload['action'], $payload['widgetId'], $payload['timestamp'], $payload['initTimestamp']) || // ensure the payload is valid.
            ($payload['action'] !== 'LOAD' && !isset($payload['challengeId'])) || // ensure the challenge ID is present, except for the init request.
            !is_numeric($payload['timestamp']) || // ensure the timestamp is a number.
            !is_numeric($payload['initTimestamp']) || // ensure the initialization timestamp is a number.
            !in_array($payload['action'], self::VALID_ACTION_TYPES, true) // ensure the action type is known.
        ) {
            $this->badRequest();
            return;
        }

        // Validate the captcha token.
        if (!$this->validateToken($payload)) {
            $this->tokenError();
            return;
        }

        // Note: JS timestamps are in milliseconds.
        $currentTimestamp = Utils::getCurrentTimeInMilliseconds();
        $requestTimestamp = $payload['timestamp'];
        $scriptTimestamp = $payload['initTimestamp'];

        // Validate the payload timestamps.
        if (
            ($requestTimestamp > $currentTimestamp || $scriptTimestamp > $currentTimestamp) || // ensure the timestamps are older than the current time.
            ($scriptTimestamp > $requestTimestamp) // ensure the script init timestamp is older than the request timestamp.
        ) {
            $this->badRequest();
            return;
        }

        switch ($payload['action']) {
            case 'LOAD':

                // Validate the theme name. Fallback to light.
                $theme = (isset($payload['theme']) && is_string($payload['theme'])) ? $payload['theme'] : 'light';

                http_response_code(200);
                header('Content-type: text/plain');
                exit($this->challenge->initialize($payload['widgetId'])->generate($theme));
            case 'SELECTION':

                // Check if the captcha ID and required other payload data is set.
                if (!isset($payload['x'], $payload['y'], $payload['width'])) {
                    $this->badRequest();
                }

                $challenge = $this->challenge->initialize($payload['widgetId'], $payload['challengeId']);
                $result = $challenge->makeSelection($payload['x'], $payload['y'], $payload['width']);

                http_response_code(200);
                header('Content-type: text/plain');
                exit($result);
            default:
                break;
        }
    }

    /**
     * Validates the payload and possibly the header tokens.
     *
     * @param array $payload The request payload to validate.
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
     */
    private function tokenError(): void
    {
        http_response_code(200);
        header('Content-type: text/plain');
        exit(Payload::encode(['error' => 'invalid-form-token']));
    }

    /**
     * Exits the request with a 400 Bad Request status.
     */
    private function badRequest(): void
    {
        http_response_code(400);
        exit;
    }

    /**
     * Checks if the current HTTP request was an Ajax request.
     */
    private function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }
}
