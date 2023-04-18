<?php

namespace IconCaptcha\Challenge;

use IconCaptcha\Token\AbstractToken;
use IconCaptcha\Utils;

class Validator
{
    private const CAPTCHA_FIELD_INIT = 'ic-rq';

    private const CAPTCHA_FIELD_WIDGET_ID = 'ic-wid';

    private const CAPTCHA_FIELD_CHALLENGE_ID = 'ic-cid';

    private const CAPTCHA_FIELD_HONEYPOT = 'ic-hp';

    private array $options;

    private $storage;

    public function __construct($storage, $options)
    {
        $this->storage = $storage;
        $this->options = $options;
    }

    /**
     * Validates the user form submission. If the captcha is incorrect, it
     * will set the global error variable and return FALSE, else TRUE.
     *
     * @param array $request The HTTP POST request variable ($_POST).
     *
     * @return ValidationResult The validation response.
     */
    public function validate(array $request): ValidationResult
    {
        // Make sure the form data is set.
        if (empty($request)) {
            return $this->createFailedResponse('empty-request');
        }

        // Check if a challenge was requested.
        if (!isset($request[self::CAPTCHA_FIELD_INIT])) {
            return $this->createFailedResponse('unsolved-challenge');
        }

        // Check if the honeypot value is set. Normal users will have this field empty.
        if (isset($request[self::CAPTCHA_FIELD_HONEYPOT]) && !empty($request[self::CAPTCHA_FIELD_HONEYPOT])) {
            return $this->createFailedResponse('detected-bot');
        }

        // Check if the widget ID is set.
        if (!isset($request[self::CAPTCHA_FIELD_WIDGET_ID]) || !Utils::isUUIDv4($request[self::CAPTCHA_FIELD_WIDGET_ID])) {
            return $this->createFailedResponse('missing-or-invalid-widget-id');
        }

        // Check if the challenge ID is set.
        if (!isset($request[self::CAPTCHA_FIELD_CHALLENGE_ID]) || !Utils::isUUIDv4($request[self::CAPTCHA_FIELD_CHALLENGE_ID])) {
            return $this->createFailedResponse('missing-or-invalid-challenge-id');
        }

        // Verify if the captcha token is correct.
        $token = $request[AbstractToken::TOKEN_FIELD_NAME] ?? null;
        if (!$this->validateToken($token)) {
            return $this->createFailedResponse('invalid-security-token');
        }

        // Get the captcha identifier.
        $challengeId = $request[self::CAPTCHA_FIELD_CHALLENGE_ID];
        $widgetId = $request[self::CAPTCHA_FIELD_WIDGET_ID];

        // Initialize the session.
        $session = Utils::createSession($this->storage, $this->options, $widgetId, $challengeId);

        // Ensure the session is valid. If the original session failed to load, the $session variable
        // will contain a new session. Checking the 'requested' status should tell if this is the case.
        if ($session->requested === false || !$session->hasSessionDataLoaded()) {
            return $this->createFailedResponse('invalid-challenge');
        }

        // Make sure the session hasn't expired.
        if ($session->isExpired()) {
            return $this->createFailedResponse('expired-challenge');
        }

        // Check if the captcha was completed.
        if ($session->completed === true) {

            // Invalidate the captcha to prevent resubmission of a form on the same captcha.
            $this->invalidate($widgetId, $challengeId);
            return $this->createSuccessResponse();
        }

        return $this->createFailedResponse('unsolved-challenge');
    }

    /**
     * Invalidates the captcha session linked to the given captcha identifier.
     * The data stored inside the session will be destroyed, as the session will be unset.
     *
     * @param string $widgetId The widget identifier.
     * @param string $challengeId The captcha challenge identifier.
     */
    public function invalidate(string $widgetId, string $challengeId): void
    {
        // Unset the previous session data
        $session = Utils::createSession($this->storage, $this->options, $widgetId, $challengeId);
        $session->destroy();
    }

    /**
     * Validates the global captcha session token against the given payload token and sometimes against a header token
     * as well. All the given tokens must match the global captcha session token to pass the check. This function
     * will only validate the given tokens if the 'token' option is set to TRUE. If the 'token' option is set to anything
     * else other than TRUE, the check will be skipped.
     *
     * @param string $payloadToken The token string received via the HTTP request body.
     * @param string|null $headerToken The token string received via the HTTP request headers. This value is optional,
     * as not every request will contain custom HTTP headers and thus this token should be able to be skipped. Default
     * value is NULL. When the value is set to anything else other than NULL, the given value will be checked against
     * the captcha session token.
     * @return bool TRUE if the captcha session token matches the given tokens or if the token option is disabled,
     * FALSE if the captcha session token does not match the given tokens.
     */
    public function validateToken(string $payloadToken, string $headerToken = null): bool
    {
        // Only validate if the token option is enabled.
        if (!empty($this->options['token'])) {
            return (new $this->options['token'])->validate($payloadToken, $headerToken);
        }
        return true;
    }

    private function createSuccessResponse(): ValidationResult
    {
        return new ValidationResult(true);
    }

    private function createFailedResponse(string $status): ValidationResult
    {
        return new ValidationResult(false, $status);
    }
}
