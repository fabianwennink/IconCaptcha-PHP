<?php

namespace IconCaptcha\Session\Drivers;

use IconCaptcha\Session\Session;
use IconCaptcha\Utils;

class ServerSession extends Session
{
    private const SESSION_NAME = 'iconcaptcha';

    private const SESSION_CHALLENGES = 'challenges';

    public function __construct(array $options, string $ipAddress, string $widgetId, string $challengeId = null)
    {
        parent::__construct($options, $ipAddress, $widgetId, $challengeId);

        $this->startSession();
        $this->purgeExpired();
        $this->load();
    }

    /**
     * @inheritDoc
     */
    protected function load(): void
    {
        if ($this->exists($this->challengeId ?? '', $this->widgetId)) {
            $this->data->fromArray($_SESSION[self::SESSION_NAME][self::SESSION_CHALLENGES]["$this->widgetId:$this->challengeId"]);
        } else {
            $this->challengeId = $this->challengeId ?? $this->generateUniqueId();
        }

        $this->dataLoaded = true;
    }

    /**
     * @inheritDoc
     */
    public function save(): void
    {
        // Write the data to the session.
        $_SESSION[self::SESSION_NAME][self::SESSION_CHALLENGES]["$this->widgetId:$this->challengeId"] = $this->data->toArray();
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        unset($_SESSION[self::SESSION_NAME][self::SESSION_CHALLENGES]["$this->widgetId:$this->challengeId"]);
    }

    /**
     * @inheritDoc
     */
    protected function purgeExpired(): void
    {
        // If the session is not set yet, do nothing.
        if (!isset($_SESSION[self::SESSION_NAME][self::SESSION_CHALLENGES])) {
            return;
        }

        // Check all existing sessions, deleting the ones which are expired.
        foreach ($_SESSION[self::SESSION_NAME][self::SESSION_CHALLENGES] as $id => $session) {
            if ($session['expiresAt'] > 0 && $session['expiresAt'] < Utils::getTimeInMilliseconds()) {
                unset($_SESSION[self::SESSION_NAME][self::SESSION_CHALLENGES][$id]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function exists(string $challengeId, string $widgetId): bool
    {
        return !empty($challengeId) && !empty($widgetId)
            && isset($_SESSION[self::SESSION_NAME][self::SESSION_CHALLENGES]["$widgetId:$challengeId"]);
    }

    /**
     * Attempts to start a session, if none has been started yet.
     * @return void
     */
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }
}
