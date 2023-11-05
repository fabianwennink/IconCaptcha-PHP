<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Token;

class IconCaptchaToken extends AbstractToken implements TokenInterface
{
    /**
     * The key of the session containing the captcha data.
     */
    private const SESSION_NAME = 'iconcaptcha';

    /**
     * The key of the session containing the token.
     */
    private const SESSION_TOKEN = 'token';

    /**
     * @inheritDoc
     */
    public static function render(): string
    {
        $token = (new self())->get();
        $name = self::TOKEN_FIELD_NAME;

        return "<input type='hidden' name='$name' value='$token' />";
    }

    /**
     * @inheritDoc
     */
    public function get(): string
    {
        // Try to load an existing token from the session.
        $existingToken = $this->retrieve();

        // If a token exists, return it.
        if (!empty($existingToken)) {
            return $existingToken;
        }

        // When no token exists, generate one.
        $token = $this->generate();

        // Save the token to the session.
        $this->store($token);

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $payloadToken, string $headerToken = null): bool
    {
        $sessionToken = $this->retrieve();

        // Validate the token.
        return $this->compareToken($sessionToken, $payloadToken, $headerToken);
    }

    /**
     * Saves the given token to the session.
     *
     * @param string $token The token to store.
     */
    private function store(string $token): void
    {
        $this->startSession();

        $_SESSION[self::SESSION_NAME][self::SESSION_TOKEN] = $token;
    }

    /**
     * Returns the token stored in the session.
     *
     * @return string|null The token, or NULL if there is no token.
     */
    private function retrieve(): ?string
    {
        $this->startSession();

        return $_SESSION[self::SESSION_NAME][self::SESSION_TOKEN] ?? null;
    }

    /**
     * Attempts to start a session, if none has been started yet.
     */
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }
}
