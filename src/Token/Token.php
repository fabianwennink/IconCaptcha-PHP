<?php

namespace IconCaptcha\Token;

class Token extends AbstractToken implements TokenInterface
{
    const SESSION_NAME = 'iconcaptcha';

    const SESSION_TOKEN = 'token';

    /**
     * @inheritDoc
     */
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
        $token = $self->generate();

        // Save the token to the session.
        $self->store($token);

        return $token;
    }

    /**
     * @inheritDoc
     */
    public static function render()
    {
        $token = self::get();
        $name = self::TOKEN_FIELD_NAME;

        return "<input type='hidden' name='$name' value='$token' />";
    }

    /**
     * @inheritDoc
     */
    public function validate($payloadToken, $headerToken = null)
    {
        $sessionToken = $this->retrieve();

        // Validate the token.
        return $this->compareToken($sessionToken, $payloadToken, $headerToken);
    }

    /**
     * Saves the given token to the session.
     * @param string $token The token to store.
     */
    private function store($token)
    {
        $this->startSession();

        $_SESSION[self::SESSION_NAME][self::SESSION_TOKEN] = $token;
    }

    /**
     * Returns the token stored in the session.
     * @return string|null The token, or NULL if there is no token.
     */
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
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }
}
