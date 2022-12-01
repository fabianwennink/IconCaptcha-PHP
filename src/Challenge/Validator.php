<?php

namespace IconCaptcha\Challenge;

use IconCaptcha\Token\AbstractToken;
use IconCaptcha\Utils;

class Validator
{
    const CAPTCHA_FIELD_ID = 'ic-hf-id';

    const CAPTCHA_FIELD_HONEYPOT = 'ic-hf-hp';

    private array $options;

    public function __construct($options)
    {
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
            return $this->createFailedResponse(3, $this->options['messages']['empty_form']);
        }

        // Check if the captcha ID is set.
        if (!isset($request[self::CAPTCHA_FIELD_ID]) || !is_numeric($request[self::CAPTCHA_FIELD_ID])
            || !$this->options['session']::exists($request[self::CAPTCHA_FIELD_ID])) {
            return $this->createFailedResponse(4, $this->options['messages']['invalid_id']);
        }

        // Check if the honeypot value is set.
        if (!isset($request[self::CAPTCHA_FIELD_HONEYPOT]) || !empty($request[self::CAPTCHA_FIELD_HONEYPOT])) {
            return $this->createFailedResponse(5, $this->options['messages']['invalid_id']);
        }

        // Verify if the captcha token is correct.
        $token = (isset($request[AbstractToken::TOKEN_FIELD_NAME])) ? $request[AbstractToken::TOKEN_FIELD_NAME] : null;
        if (!$this->validateToken($token)) {
            return $this->createFailedResponse(6, $this->options['messages']['form_token']);
        }

        // Get the captcha identifier.
        $identifier = $request[self::CAPTCHA_FIELD_ID];

        // Initialize the session.
        $session = $this->createSession($identifier);

        // Make sure the session hasn't expired.
        if($session->expiresAt > 0 && $session->expiresAt < Utils::getTimeInMilliseconds()) {
            return $this->createFailedResponse(1, $this->options['messages']['session_expired']);
        }

        // Check if the captcha was completed.
        if ($session->completed === true) {

            // Invalidate the captcha to prevent resubmission of a form on the same captcha.
            $this->invalidate($identifier);
            return $this->createSuccessResponse();
        }

        // TODO create new error message stating the form wasn't completed.
        return $this->createFailedResponse(1, $this->options['messages']['wrong_icon']);
    }

    /**
     * Invalidates the captcha session linked to the given captcha identifier.
     * The data stored inside the session will be destroyed, as the session will be unset.
     *
     * @param int $identifier The identifier of the captcha.
     */
    public function invalidate(int $identifier)
    {
        // Unset the previous session data
        $session = $this->createSession($identifier);
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

    private function createFailedResponse($status, $message): ValidationResult
    {
        return new ValidationResult(false, $status, $message);
    }

    private function createSession($identifier = 0)
    {
        return new $this->options['session']($identifier);
    }
}
