<?php

namespace IconCaptcha\Storage\Session;

use IconCaptcha\Storage\StorageInterface;
use RuntimeException;

class SessionConnector implements StorageInterface
{
    /**
     * The key in which all captcha related data will be stored.
     */
    private const SESSION_NAME = 'iconcaptcha';

    /**
     * @inheritDoc
     * @return SessionStorage
     */
    public function connect(): SessionStorage
    {
        $this->startSession();

        // Initialize a new session key if it does not yet exist.
        if(!isset($_SESSION[self::SESSION_NAME])) {
            $_SESSION[self::SESSION_NAME] = [];
        }

        return new SessionStorage(self::SESSION_NAME);
    }

    /**
     * Attempts to start a session, if none has been started yet.
     *
     * @throws RuntimeException if the headers have already been sent
     */
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if(!headers_sent()) {
                session_start();
            } else {
                throw new RuntimeException('A session could not be started as the headers have already been sent.');
            }
        }
    }
}
