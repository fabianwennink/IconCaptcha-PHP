<?php

/**
 * IconCaptcha Plugin: v3.1.1
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

namespace IconCaptcha;

use IconCaptcha\Challenge\Challenge;
use IconCaptcha\Session\IconCaptchaSessionInterface;

class IconCaptcha
{
    const CAPTCHA_FIELD_ID = 'ic-hf-id';

    const CAPTCHA_FIELD_HONEYPOT = 'ic-hf-hp';

    const CAPTCHA_FIELD_TOKEN = '_iconcaptcha-token';

    /**
     * @var string A JSON encoded error message, which will be shown to the user.
     */
    private $error;

    /**
     * @var IconCaptchaSessionInterface The session containing captcha information.
     */
    private $session;

    /**
     * @var mixed Default values for all the server-side options.
     */
    private $options;

    public function __construct($options = [])
    {
        $this->options = IconCaptchaOptions::prepare($options);
    }

    /**
     * Override the options for the captcha. The given options will be merged together with the
     * default options and overwrite the default values. If any of the options are missing
     * in the given options array, they will be set with their default value.
     * @param array $options The array of options.
     */
    public function options($options)
    {
        $this->options = IconCaptchaOptions::prepare($options);
    }

    public function challenge($identifier)
    {
        $this->createSession($identifier);
        return new Challenge($this->session, $this->options);
    }

    /**
     * Returns the validation error message, or return NULL if there is no error.
     *
     * @return string|null The JSON encoded error message containing the error ID and message, or NULL.
     */
    public function getErrorMessage()
    {
        return !empty($this->error) ? json_decode($this->error)->error : null;
    }

    /**
     * Validates the user form submission. If the captcha is incorrect, it
     * will set the global error variable and return FALSE, else TRUE.
     *
     * @param array $request The HTTP POST request variable ($_POST).
     *
     * @return boolean TRUE if the captcha was correct, FALSE if not.
     */
    public function validate($request)
    {
        // Make sure the form data is set.
        if (empty($request)) {
            $this->setErrorMessage(3, $this->options['messages']['empty_form']);
            return false;
        }

        // Check if the captcha ID is set.
        if (!isset($request[self::CAPTCHA_FIELD_ID]) || !is_numeric($request[self::CAPTCHA_FIELD_ID])
            || !$this->options['session']::exists($request[self::CAPTCHA_FIELD_ID])) {
            $this->setErrorMessage(4, $this->options['messages']['invalid_id']);
            return false;
        }

        // Check if the honeypot value is set.
        if (!isset($request[self::CAPTCHA_FIELD_HONEYPOT]) || !empty($request[self::CAPTCHA_FIELD_HONEYPOT])) {
            $this->setErrorMessage(5, $this->options['messages']['invalid_id']);
            return false;
        }

        // Verify if the captcha token is correct.
        $token = (isset($request[self::CAPTCHA_FIELD_TOKEN])) ? $request[self::CAPTCHA_FIELD_TOKEN] : null;
        if (!$this->validateToken($token)) {
            $this->setErrorMessage(6, $this->options['messages']['form_token']);
            return false;
        }

        // Get the captcha identifier.
        $identifier = $request[self::CAPTCHA_FIELD_ID];

        // Initialize the session.
        $this->createSession($identifier);

        // Check if the captcha was completed.
        if ($this->session->completed === true) {

            // Invalidate the captcha to prevent resubmission of a form on the same captcha.
            $this->invalidate($identifier);
            return true;
        } else {
            // TODO create new error message stating the form wasn't completed.
            $this->setErrorMessage(1, $this->options['messages']['wrong_icon']);
        }

        return false;
    }

    /**
     * Invalidates the captcha session linked to the given captcha identifier.
     * The data stored inside the session will be destroyed, as the session will be unset.
     *
     * @param int $identifier The identifier of the captcha.
     */
    public function invalidate($identifier)
    {
        // Unset the previous session data
        $this->createSession($identifier);
        $this->session->destroy();
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
    public function validateToken($payloadToken, $headerToken = null)
    {
        // Only validate if the token option is enabled.
        if (!empty($this->options['token'])) {
            return (new $this->options['token'])->validate($payloadToken, $headerToken);
        }
        return true;
    }

    /**
     * Tries to load/initialize a captcha session with the given captcha identifier.
     * When an existing session is found, it's data will be loaded, else a new session will be created.
     *
     * @param int $identifier The identifier of the captcha.
     */
    private function createSession($identifier = 0)
    {
        // Load the captcha session for the current identifier.
        $this->session = new $this->options['session']($identifier);
    }

    /**
     * Sets the global {@see $error} property, which can be retrieved with the {@see getErrorMessage} function.
     * @param int $id The identifier of the error message.
     * @param string $message The error message to set.
     */
    private function setErrorMessage($id, $message)
    {
        $this->error = json_encode(['id' => $id, 'error' => $message]);
    }
}
