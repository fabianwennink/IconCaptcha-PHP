<?php

namespace IconCaptcha;

class IconCaptchaToken implements IconCaptchaTokenInterface
{
    const SESSION_NAME = 'iconcaptcha';

    const SESSION_TOKEN = 'token';

    const CAPTCHA_TOKEN_LENGTH = 20;

    public static function get()
    {
        $self = new self();

        // Try to load an existing token from the session.
        $existingToken = $self->retrieve();

        // If a token exists, return it.
        if(!empty($existingToken)) {
            return $existingToken;
        }

        // When no token exists, generate one.
        return $self->generate();
    }

    public static function render()
    {
        $token = self::get();
        $name = IconCaptcha::CAPTCHA_FIELD_TOKEN;

        return "<input type='hidden' name='$name' value='$token' />";
    }

    /**
     * @inheritDoc
     */
    public function validate($payloadToken, $headerToken = null)
    {
        $sessionToken = $this->retrieve();

        // If the token is empty but the option is enabled, the token was never requested.
        if (empty($sessionToken)) {
            return false;
        }

        // Validate the payload and header token (if set) against the session token.
        if ($headerToken !== null) {
            return $sessionToken === $payloadToken && $sessionToken === $headerToken;
        } else {
            return $sessionToken === $payloadToken;
        }
    }

    private function generate()
    {
        // Create a secure captcha session token.
        if (function_exists('random_bytes')) {
            // Only available for PHP 7 or higher.
            try {
                $token = bin2hex(random_bytes(self::CAPTCHA_TOKEN_LENGTH));
            } catch (\Exception $e) {
                // Using a fallback in case of an exception.
                $token = str_shuffle(md5(uniqid(rand(), true)));
            }
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            // Only available when the OpenSSL extension is installed.
            $token = bin2hex(openssl_random_pseudo_bytes(self::CAPTCHA_TOKEN_LENGTH));
        } else {
            // If not on PHP 7+ or having the OpenSSL extension installed, use this fallback.
            $token = str_shuffle(md5(uniqid(rand(), true)));
        }

        // Save the token to the session.
        $this->store($token);

        return $token;
    }

    private function store($token)
    {
        $this->startSession();

        $_SESSION[self::SESSION_NAME][self::SESSION_TOKEN] = $token;
    }

    private function retrieve()
    {
        $this->startSession();

        return isset($_SESSION[self::SESSION_NAME][self::SESSION_TOKEN])
            ? $_SESSION[self::SESSION_NAME][self::SESSION_TOKEN] : null;
    }

    /**
     * Attempts to start a session, if none has been started yet.
     * @return void
     */
    private function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
