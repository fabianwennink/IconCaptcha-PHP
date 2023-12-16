<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Challenge;

use IconCaptcha\Session\SessionFactory;
use IconCaptcha\Token\AbstractToken;
use IconCaptcha\Utils;

class Validator
{
    /**
     * The name of the form input field containing whether the captcha was initialized.
     */
    private const CAPTCHA_FIELD_INIT = 'ic-rq';

    /**
     * The name of the form input field containing the widget identifier.
     */
    private const CAPTCHA_FIELD_WIDGET_ID = 'ic-wid';

    /**
     * The name of the form input field containing the challenge identifier.
     */
    private const CAPTCHA_FIELD_CHALLENGE_ID = 'ic-cid';

    /**
     * The name of the form input field used as a honeypot.
     */
    private const CAPTCHA_FIELD_HONEYPOT = 'ic-hp';

    /**
     * @var array The captcha options.
     */
    private array $options;

    /**
     * @var mixed The storage container.
     */
    private $storage;

    /**
     * Creates a new challenge validator instance.
     *
     * @param mixed $storage The storage container.
     * @param array $options The captcha options.
     */
    public function __construct($storage, array $options)
    {
        $this->storage = $storage;
        $this->options = $options;
    }

    /**
     * Validates the user form submission. If the captcha is incorrect, it
     * will set the global error variable and return FALSE, else TRUE.
     *
     * @param array $request The HTTP POST request variable ($_POST).
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
        $token = $request[AbstractToken::TOKEN_FIELD_NAME] ?? '';
        if (!is_string($token) || !$this->validateToken($token)) {
            return $this->createFailedResponse('invalid-security-token');
        }

        // Get the captcha identifier.
        $challengeId = $request[self::CAPTCHA_FIELD_CHALLENGE_ID];
        $widgetId = $request[self::CAPTCHA_FIELD_WIDGET_ID];

        // Initialize the session.
        $session = SessionFactory::create(
            $this->storage,
            $this->options['session']['driver'] ?? $this->options['storage']['driver'],
            $this->options['session'],
            Utils::getIpAddress($this->options['ipAddress']),
            $widgetId, $challengeId
        );

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

            // Invalidate the captcha to prevent resubmission.
            $session->destroy();

            return $this->createSuccessResponse();
        }

        return $this->createFailedResponse('unsolved-challenge');
    }

    /**
     * Validates the global captcha session token against the given payload token and against a header token
     * as well if present. All the given tokens must match the global captcha session token to pass the check.
     *
     * This function will only validate the given tokens if the 'token' option is set to TRUE. If the 'token'
     * option is set to anything else other than TRUE, the check will be skipped.
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

    /**
     * Creates a success response object.
     *
     * @return ValidationResult The successful validation response.
     */
    private function createSuccessResponse(): ValidationResult
    {
        return new ValidationResult(true);
    }

    /**
     * Creates a failed response object.
     *
     * @param string $status The status message for the failed response.
     * @return ValidationResult The failed validation response.
     */
    private function createFailedResponse(string $status): ValidationResult
    {
        return new ValidationResult(false, $status);
    }
}
