<?php

namespace IconCaptcha\Session\Drivers;

use IconCaptcha\Session\Session;
use IconCaptcha\Storage\Session\SessionStorage;
use IconCaptcha\Utils;

class ServerSession extends Session
{
    /**
     * @var string The key used to store the challenges at in the session.
     */
    private string $sessionKey = 'challenges';

    private SessionStorage $storage;

    public function __construct(SessionStorage $storage, array $options, string $ipAddress, string $widgetId, string $challengeId = null)
    {
        parent::__construct($options, $ipAddress, $widgetId, $challengeId);

        $this->storage = $storage;

        $this->purgeExpired();
        $this->load();
    }

    /**
     * @inheritDoc
     */
    protected function load(): void
    {
        if ($this->exists($this->challengeId ?? '', $this->widgetId)) {
            $this->puzzle->fromArray(
                $this->storage->read("$this->sessionKey.$this->widgetId:$this->challengeId")
            );
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
        $this->storage->write("$this->sessionKey.$this->widgetId:$this->challengeId", $this->puzzle->toArray());
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        $this->storage->remove("$this->sessionKey.$this->widgetId:$this->challengeId");
    }

    /**
     * @inheritDoc
     */
    protected function purgeExpired(): void
    {
        // If the session is not set yet, do nothing.
        if (!$this->storage->exists($this->sessionKey)) {
            return;
        }

        // Check all existing sessions, deleting the ones which are expired.
        foreach ($this->storage->read($this->sessionKey) as $id => $session) {
            if ($session['expiresAt'] > 0 && $session['expiresAt'] < Utils::getTimeInMilliseconds()) {
                $this->storage->remove("$this->sessionKey.$id");
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function exists(string $challengeId, string $widgetId): bool
    {
        return !empty($challengeId) && !empty($widgetId)
            && $this->storage->exists("$this->sessionKey.$widgetId:$challengeId");
    }
}
